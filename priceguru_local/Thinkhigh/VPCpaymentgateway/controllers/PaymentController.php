<?php

class Thinkhigh_VPCpaymentgateway_PaymentController extends Mage_Core_Controller_Front_Action {
	// The redirect action is triggered when someone places an order
	public function redirectAction() { 
        $this->loadLayout();
		$post_data=Mage::getModel('vpcpaymentgateway/paymentconnection')->getPostData();
		$block = $this->getLayout()->createBlock('core/template')->setTemplate('vpcpaymentgateway/redirect.phtml');
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}

	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if($this->getRequest()->isGet()) {
			
            $response=$this->getRequest()->getParams();
            /********************************************************************************
            * 
            * -------------------------------------------------------------------------------
            * CHECK RETURNED HASH with sent secureHASH for INTEGRITY....
            * REMAINING
            * Validate HASH
            * vpc_ReturnAuthResponseData  
            * 
            * 
            * 
            *********************************************************************************/
            if($response['vpc_TxnResponseCode']==="0"){
                //Success
                $response['vpc_TransactionNo'];
                $validated=true;
            }else {//if($response['vpc_TxnResponseCode']==="7"){
                $response['vpc_Message'];//Error message 
            }
            $ResponseMessage=Mage::helper('vpcpaymentgateway')->getResultDescription($response['vpc_TxnResponseCode']);

			$orderId = $response['vpc_OrderInfo']; // Generally sent by gateway

			if($validated) {
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($orderId);
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
				
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				
				$order->save();
			
				Mage::getSingleton('checkout/session')->unsQuoteId();
				
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
			}
			else {
				// There is a problem in the response we got
				$this->cancelAction();
                Mage::getSingleton('checkout/session')->setErrorMessage($ResponseMessage);
                Mage::log('MIGS Payment gateway error: '.$ResponseMessage.' ['.$response['vpc_Message'].' ]');
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect('');
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
			}
        }
	}
}