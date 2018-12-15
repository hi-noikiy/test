<?php

class Collinsharper_Beanstreaminterac_StandardController extends Mage_Core_Controller_Front_Action
{
    
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

 
    public function getStandard()
    {
        return Mage::getSingleton('beanstreaminterac/standard');
    }

    /**
     * When a customer chooses beanstreaminterac on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
			$session = Mage::getSingleton('checkout/session');
		if(strstr(Mage::getVersion(),'1.3.'))
		{
			$session->setBeanstreaminteracStandardQuoteId($session->getQuoteId());
			$session->unsQuoteId();
		}
		else
		{
			$session->setBeanstreaminteracStandardQuoteId($session->getLastRealOrderId());			
		}
			$this->getResponse()->setBody($this->getLayout()->createBlock('beanstreaminterac/standard_redirect')->toHtml());
    }

    /**
     * When a customer cancel payment from beanstreaminterac.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
		mage::log(__CLASS__ . __FUNCTION__ . __LINE__ . " new direction ");
		$session->addError('Your Transaction is not successful or not completed.');
		$session->addError('Your order has been cancelled.');
		if($this->isOldMage())
		{
			$session->setQuoteId($session->getBeanstreaminteracStandardQuoteId(true));
		}

        // cancel order
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
	   $cart = Mage::getSingleton('checkout/cart');
        $cartTruncated = false;
        /* @var $cart Mage_Checkout_Model_Cart */

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e){
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                }
               // $this->_redirect('*/*/history');
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e,
                    Mage::helper('checkout')->__('Cannot add the item to shopping cart.')
                );
				}
		}

                $order->cancel()->save();
            }
        }

  
        //and then redirect to checkout one page
		mage::log(__CLASS__ . __FUNCTION__ . __LINE__ . " new direction ");
	//	$this->getResponse()->setBody($this->getLayout()->createBlock('beanstreaminterac/standard_cancel')->toHtml());
        $this->_redirect('checkout/cart');
    }
	
	function isOldMage()
	{
	 return strstr(Mage::getVersion(),'1.3.') !== false;
	}

    /**
     * when beanstreaminterac returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
       		if($this->isOldMage())
		{
			$session->setQuoteId($session->getBeanstreaminteracStandardQuoteId(true));
			Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
		}
		else
		{
		
		}
		
		
		
		
		
		 $order = Mage::getModel('sales/order');
        $this->getStandard()->setSuccessFormData($this->getRequest()->getParams());       

        // if (!$this->getRequest()->isPost()) {
            // $this->_redirect('');
            // return;
        // }

        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
        if(!$order->getId()) 
		{
		    Mage::log(__FILE__ . " This will all break we can't find an order in the session");
		}
	
		$sArr = array(
		//            'requestType'       => 'BACKEND',
					'merchant_id'       => Mage::getStoreConfig('payment/beanstreaminterac/merchant_id'),
					'trnAmount'			=> $order->getTotalDue(),
		'IDEBIT_INVOICE'		=> $this->getStandard()->getSuccessFormData('IDEBIT_INVOICE'),
		'IDEBIT_MERCHDATA'		=> $this->getStandard()->getSuccessFormData('IDEBIT_MERCHDATA'),
		'IDEBIT_VERSION'		=> $this->getStandard()->getSuccessFormData('IDEBIT_VERSION'),
		'IDEBIT_ISSLANG'		=> $this->getStandard()->getSuccessFormData('IDEBIT_ISSLANG'),
		'IDEBIT_ISSCONF'		=> $this->getStandard()->getSuccessFormData('IDEBIT_ISSCONF'),
		'IDEBIT_ISSNAME'		=> $this->getStandard()->getSuccessFormData('IDEBIT_ISSNAME'),
		'IDEBIT_TRACK2'			=> $this->getStandard()->getSuccessFormData('IDEBIT_TRACK2'),
		'IDEBIT_FUNDEDURL'		=> $this->getStandard()->getSuccessFormData('IDEBIT_FUNDEDURL'),
		'IDEBIT_NOTFUNDEDURL'	=> $this->getStandard()->getSuccessFormData('IDEBIT_NOTFUNDEDURL')
		);

	if(Mage::getStoreConfig('payment/interac/store_id'))
	  $sArr['username']	= Mage::getStoreConfig('payment/beanstreaminterac/store_id');

	if(Mage::getStoreConfig('payment/interac/password'))
	  $sArr['password'] = Mage::getStoreConfig('payment/beanstreaminterac/password');

  $url="https://www.beanstream.com/scripts/Process_transaction_auth.asp"; 
	
	$fields = '';
foreach($sArr as $k=>$v)
  $fields .= $k.'='.$v.'&';
  $fields = trim($fields,'&');

  
	$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $qresult = curl_exec($ch); 
	if(Mage::getStoreConfig('payment/beanstreaminterac/test_mode'))
	{
		Mage::log("sent to beanstream". urldecode($fields));
		Mage::log("return from beanstream". urldecode($qresult));
	}

        $response = array();
        $res = explode( '&', $qresult);
        foreach($res as $v)
        {
            $d = explode('=',$v);
             $response[$d[0]] = rawurldecode($d[1]);
        }
  
				
        if (curl_errno($ch)) 
		{
		// big error hdere
		Mage::log("interac validation Payment FAILED!". urldecode($qresult));
        } 
		else 
		{
            curl_close($ch);
		}		

		$this->getStandard()->setSuccessFormData($response);
		if(Mage::getStoreConfig('payment/beanstreaminterac/test_mode'))
		{
			Mage::log(__CLASS__ . __FUNCTION__ . "good interac" . print_r($response,1));
		}

		if(!array_key_exists('trnApproved', $response)  || $response['trnApproved'] != 1) 
		{
						   $order->addStatusToHistory(
						$order->getStatus(), // keep order status/state
						Mage::helper('beanstreaminterac')->__('Error in creating an invoice', true),
						$notified = true
				   )->save;
            //Mage::getSingleton('checkout/cart')->getCheckoutSession()->addNotice($this->__("Interac Payment failed. "));
            //$this->_redirect('checkout/onepage', array('_secure'=>true));
            $this->_interacresponse = print_r($response,1);
            Mage::getSingleton('checkout/session')->setInteracError(print_r($response,1));
            $this->_forward('cancel');
			return;
		}
		
	   $this->getStandard()->successSubmit();

        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getInteracStandardQuoteId(true));
        $session->setTransId($order->getId());
        /**
         * set the quote as inactive after back from paypal
         */
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        /**
         * send confirmation email to customer
         */
  
        // if($order->getId()){
            // $order->sendNewOrderEmail();
        // }
			//Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
			foreach( Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item ){
    Mage::getSingleton('checkout/cart')->removeItem( $item->getId() )->save();
}
       				$this->loadLayout();
		
        $this->_initLayoutMessages('checkout/session');        
 
        $this->renderLayout();    
		
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }


	
 
    
}
