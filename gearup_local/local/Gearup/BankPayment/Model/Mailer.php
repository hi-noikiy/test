<?php
class Gearup_BankPayment_Model_Mailer extends Gearup_BankPayment_Model_Mailer_Amasty_Pure
{
	public function send()
    {
    	$emailTemplate = Mage::getModel('core/email_template');
		$paymentMethodAsneeded = false;
        /* Check if now we are sending order confirmation email */
		 $templateParams = $this->getTemplateParams();
        $storeId = $this->getStoreId();

                /** @var $order Mage_Sales_Model_Order */
                $order = @$templateParams['order'];
				$invoice = @$templateParams['invoice'];				
				if(isset($order) || isset($invoice)){
					if(!$order)
						$order = $invoice;
					$paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode(); 

					if (($paymentMethodCode == 'bankpayment') || ($paymentMethodCode == 'cashondelivery')){
											//Mage::log('step1', true, 'payment_debug.log');
						$paymentMethodAsneeded = true;
					}
				}
		
		
        if ($this->getTemplateId() == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId)
            || $this->getTemplateId() == Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId)) {           
                if ($paymentMethodAsneeded){
					//Mage::log('step2', true, 'payment_debug.log');
					return false;     
					
				}
        }
		
		 if ( $this->getTemplateId() == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId) && $paymentMethodAsneeded){
			    $this->setTemplateId(Mage::getStoreConfig( Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId));		
				
		 }
			   
         if($this->getTemplateId() == Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId) && $paymentMethodAsneeded){			 
			$this->setTemplateId(Mage::getStoreConfig( Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId));			   			 
		 }

        $helper = Mage::helper('emailattachments');
        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);
            $helper->debug('NEW EMAIL------------------------------------------');
            $helper->debug($emailInfo->getToNames());
			$emailTemplate['paymentMethodAsneeded'] = $paymentMethodAsneeded;
            $this->dispatchAttachEvent($emailTemplate);
            // Handle "Bcc" recepients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $ret = $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                    $this->getTemplateId(),
                    $this->getSender(),
                    $emailInfo->getToEmails(),
                    $emailInfo->getToNames(),
                    $this->getTemplateParams(),
                    $this->getStoreId()
                );
            $helper->debug('FINISHED SENDING - Sent Status: ' . (bool)$ret->getSentSuccess());
        }
        return $this;
    }



    /**
     * Adds an attachment to the current email template
     *
     * @param Mage_Core_Model_Email_Template $template
     * @param Zend_Pdf $pdf
     * @param string $filename
     * @return Atwix_ConfirmationInvoice_Model_Email_Template_Mailer
     */
    public function addAttachment($template, Zend_Pdf $pdf, $filename)
    {
        $file = $pdf->render();
        $attachment = $template->getMail()->createAttachment($file);
        if(!is_object($attachment)) return $this;
        $attachment->type = 'application/pdf';
        $attachment->filename = $filename;

        return $this;
    }
	
	
	 public function dispatchAttachEvent($emailTemplate)
    {
        $storeId = $this->getStoreId();
        $templateParams = $this->getTemplateParams();

        //compare template id to work out what we are currently sending
        switch ($this->getTemplateId()) {

        //Order
        case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
			if(!isset($emailTemplate['paymentMethodAsneeded'])){
							
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_order',
                array(
                    'update'   => false,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['order']
                )
            );
			}else{
				//Mage::log('step3', true, 'payment_debug.log');
				Mage::dispatchEvent(
                'fooman_emailattachments_before_send_invoice',
					array(
						'update'   => false,
						'template' => $emailTemplate,
						'object'   => $templateParams['invoice']
					)
				);				
			}
            break;
        //Order Updates
        case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_order',
                array(
                    'update'   => true,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['order']
                )
            );
            break;

        //Invoice
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_invoice',
                array(
                    'update'   => false,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['invoice']
                )
            );
            break;

        //Invoice Updates
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_invoice',
                array(
                    'update'   => true,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['invoice']
                )
            );
            break;

        //Shipment
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_shipment',
                array(
                    'update'   => false,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['shipment']
                )
            );
            break;

        //Shipment Updates
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_shipment',
                array(
                    'update'   => true,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['shipment']
                )
            );
            break;

        //Creditmemo
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_creditmemo',
                array(
                    'update'   => false,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['creditmemo']
                )
            );
            break;

        //Creditmemo Updates
        case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
        case Mage::getStoreConfig(
            Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId
        ):
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send_creditmemo',
                array(
                    'update'   => true,
                    'template' => $emailTemplate,
                    'object'   => $templateParams['creditmemo']
                )
            );
            break;
        default:
            Mage::dispatchEvent(
                'fooman_emailattachments_before_send',
                array(
                    'template' => $emailTemplate,
                    'params'   => $templateParams
                )
            );
        }
    }
	
	
}
		