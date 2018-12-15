<?php

class Ktpl_Customreport_IndexController extends Mage_Core_Controller_Front_Action
{
	public function cimcheckoutAction()
    {
        $data = $this->getRequest()->getParams();
        Mage::getSingleton('core/session')->setCheckoutcredit($data);
        echo $this->getRequest()->getParam('product');
        return;
        //$this->_redirect('ajaxcart/index/cimcheckoutform');
    }

    public function savecimorderAction()
    {
        $data = $this->getRequest()->getPost();
        $productdata = Mage::getSingleton('core/session')->getCheckoutcredit();

        $result = array();
        if(!isset($data['cimtermcondition'])) {
            $result['error'] = true;
            $result['error_messages'] = "Please read and accept Terms and condition";
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $buyInfo = array(
            'product' => $productdata['product'],
            'options' => $productdata['options'],
            'qty' => 1
        );

        $cimmodel = Mage::getModel('customreport/cimorder');
        $Id = $cimmodel->createOrder($buyInfo, $data);
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

	public function creditformAction() {
        if(!Mage::getSingleton('core/session')->getCheckoutcredit()) {
            $this->_redirect('checkout/cart');
            return;
        }
		$this->loadLayout();
        $this->renderLayout();
	}

    public function cimordersuccessAction() {
        $this->loadLayout();
        Mage::getSingleton('core/session')->unsetData('checkoutcredit');
        $this->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        $post['collect'] =  $post['collect'][0];
        if ( $post ) {
            
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;
                 $fileName = '';
               /* if (isset($_FILES['attach']['name']) && $_FILES['attach']['name'] != '') {
                    try {
                        $fileName       = $_FILES['attach']['name'];
                        $fileExt        = strtolower(substr(strrchr($fileName, ".") ,1));
                        $fileNamewoe    = rtrim($fileName, $fileExt);
                       // $fileName       = preg_replace('/\s+', '', $fileNamewoe) . time() . '.' . $fileExt;
                         $dat = date('d-m-y');
                        $uploader       = new Varien_File_Uploader('attach');
                        //$uploader->setAllowedExtensions(array('doc', 'docx','pdf'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'repair' . DS . $dat;
                        if(!is_dir($path)){
                            mkdir($path, 0777, true);
                        }
                        $uploader->save($path . DS, $fileName );

                    } catch (Exception $e) {
                        $error = true;
                    }
                } 
                    if ($error) {
                    throw new Exception();
                } */
                    $templateId = 19; // Enter you new template ID
                    $senderName = 'Priceguru';  //Get Sender Name from Store Email Addresses
                    $senderEmail = $post['email'];  //Get Sender Email Id from Store Email Addresses
                    $sender = array('name' => $senderName,
                        'email' => $senderEmail);
                    $email_template  = Mage::getModel('core/email_template')->loadDefault($templateId);
                            
                    $recipients = [
                        'aftersales@priceguru.mu' => 'After Sale',
                        'eric@priceguru.mu' => 'Eric',
                        'info@priceguru.mu' => 'Info'
                    ];

                    $store = Mage::app()->getStore()->getId();
                                    
                    $mailTemplate = Mage::getModel('core/email_template');
                     $attachmentFilePath = $path . DS . $fileName;
                if(file_exists($attachmentFilePath)){                
                    $mailTemplate->getMail()->createAttachment(
                        file_get_contents(Mage::getBaseDir('base') . '/media/repair/'.$dat.'/'.$fileName), //location of file
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        basename($fileName)
                    );
                }    
                     if(!$mailTemplate
                            ->sendTransactional($templateId, $sender, array_keys($recipients), array_values($recipients),  array('data' => $postObject), $store)) {
                        Mage::log($senderEmail, null, 'ktpl_repair_fail-'.date("Y-m-d").'.log');
                    }
                    else{
                        Mage::log($senderEmail, null, 'ktpl_repair_success-'.date("Y-m-d").'.log');
                    }
               
                    
                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);
                unlink($path . DS . $fileName);
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('repairs');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('repairs');
                return;
            }

        } else {
            $this->_redirect('repairs');
        }
    }
}