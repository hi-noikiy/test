<?php

class Ktpl_Customreport_Adminhtml_PickuporderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/sales_pickup')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /*public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customreport/adminhtml_pickuporder/grid')->toHtml()); 
    }*/

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'pickuporder.csv';
        $content = $this->getLayout()->createBlock('customreport/adminhtml_pickuporder_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'pickuporder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_pickuporder_grid')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function updateRowFieldsAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if($data = $this->getRequest()->getPost()) {
            if($data['wholesaleprice'] == "") { 
                $data['wholesaleprice'] == NULL; 
            } 
            
            if($data['deposit'] == "") { 
                $data['deposit'] == NULL; 
            }
            
            if ($fieldId) {
                $model = Mage::getModel('customreport/salespickuporder');
                $model->setData($data)->setId($fieldId);
                $model->save();
               
                $upmodel = Mage::getModel('customreport/salespickuporder')->load($fieldId);
                $cid = $upmodel->getOrderId(); 
                $cimmodel = Mage::getModel('customreport/salescimorder')->load($cid,'order_id');
                $order = Mage::getModel('sales/order')->load($upmodel->getRealOrderId());
                
                if($data['delivery_comment'] != "") {
                    $order->addStatusHistoryComment($data['delivery_comment']);
                    $order->save();
                }
                $order = Mage::getmodel('sales/order')->load($upmodel->getRealOrderId());
                if($data['status'] == '3' && $upmodel->getDelivery() == NULL) {
                    $deliverymodel = Mage::getModel('customreport/salesdeliveryorder')->load($upmodel->getPickupId(), 'pickupid');
                    
                    $deliverymodel->setOrderId($upmodel->getOrderId());
                    $deliverymodel->setRealOrderId($upmodel->getRealOrderId());
                    $deliverymodel->setPickupid($upmodel->getPickupId());
                    $deliverymodel->setRepairId($upmodel->getRepairId());
                    $deliverymodel->setCustomerName($upmodel->getCustomerName());
                    $deliverymodel->setProductName($upmodel->getProductName());
                    $deliverymodel->setSku($upmodel->getSku());
                    $deliverymodel->setQty($upmodel->getQty());
                    $deliverymodel->setPaymentMethod($upmodel->getPaymentMethod());
                    $deliverymodel->setCustomerComment($order->getOnestepcheckout_order_comment());
                    $deliverymodel->setPickup_status($upmodel->getPickupDone());
                    $deliverymodel->setPickup_comment($upmodel->getPickup_comment());
                    $deliverymodel->setPickup_date($upmodel->getPickup_date());
                    $deliverymodel->setOrderStatus($order->getStatus());
                    if(!$deliverymodel->getOrderCreatedDate()){
                        $deliverymodel->setAttributes($upmodel->getAttributes());
                        $deliverymodel->setDeposit($cimmodel->getDeposit());
                        $deliverymodel->setRegion($upmodel->getRegion());
                        $deliverymodel->setAddress($upmodel->getAddress());
                        $deliverymodel->setTelephone($upmodel->getTelephone());
                        $deliverymodel->setStatus(1);
                        $deliverymodel->setOrderCreatedDate(date('Y-m-d H:i:s'));
                        $deliverymodel->setDeliveryDateTime($upmodel->getDeliveryDate());
                    }    
                    $deliverymodel->save();
                    
                    $start_date = new DateTime($upmodel->getOrderCreatedDate());
                    $since_start = $start_date->diff(new DateTime());
                    $time = '';
                    if($since_start->days > 0){
                        $time .= $since_start->days.' Days : ';
                    }
                    $time .= $since_start->h.' Hours : '.$since_start->i .' Minutes' ;
                    $upmodel->setLeadtime($time);
                    $upmodel->save();
                    echo $time;
                }
            }
        }
    }

    public function updatePickupaddressAction() {
        $wholesalerid = $this->getRequest()->getParam('wholesalerid');
        $fieldId = (int) $this->getRequest()->getParam('id');

        $whole = Mage::getModel('customreport/wholesaler')->load($wholesalerid);
        $pickupmodel = Mage::getModel('customreport/salespickuporder')->load($fieldId);
        $pickupmodel->setWholesalerId($wholesalerid);    
        $pickupmodel->setPickupAddress($whole->getAddress());
        $pickupmodel->save();

        echo $whole->getAddress();
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateMarkupFieldAction() {
        $fieldId = (int) $this->getRequest()->getParam('id');
        $wholesaleprice = (int) $this->getRequest()->getParam('wholesalerprice');

        if($wholesaleprice > 0) {
            $pickupmodel = Mage::getModel('customreport/salespickuporder')->load($fieldId);
            $retail_price = $pickupmodel->getRetailPrice();
            $markup = (($retail_price - $wholesaleprice) * 100) / $retail_price;
            $markup = round($markup);
            $pickupmodel->setWholesalePrice($wholesaleprice);
            $pickupmodel->setMarkup($markup);
            $pickupmodel->save();
            echo $markup;
        }
    }

    public function sendemailAction() {
        //echo "<per>"; print_r($this->getRequest()->getParams()); exit;
       
        $cimemail = $this->getRequest()->getParam('cim-email-template');
        $template_id = "cimorder_email_".$cimemail;
        $order_id = $this->getRequest()->getParam('order_id');
        $real_id = $this->getRequest()->getParam('real_id');
        $order = Mage::getModel('customreport/salescimorder')->load($order_id, 'order_id');

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault($template_id);

        $result = array();
        $emailvars = array();
        $emailvars['order'] = $order;
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailvars);

		$mail = new Zend_Mail('UTF-8');       
        
        $mail->setFrom("info@priceguru.mu", "Priceguru.mu");
        $mail->setReplyTo('info@priceguru.mu', 'Priceguru.mu');
        $mail->addHeader('MIME-Version', '1.0');
        $mail->addHeader('Content-Transfer-Encoding', '8bit');
        $mail->addHeader('X-Mailer:', 'PHP/'.phpversion());
        $mail->addTo($order->getEmail(), $order->getCustomerName());
        $mail->setBodyHtml($processedTemplate);
        $mail->setSubject('Credit application order');
		try {
			$mail->send();
			$result['ajaxExpired'] = 1;
			$result['ajaxRedirect'] = Mage::helper("adminhtml")->getUrl("adminhtml/sales_order/view/order_id/".$real_id);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customreport')->__('CIM order email send successfully.'));
			//echo "Email sent successfully";
		}
		catch(Exception $ex) {
			$result['error'] =  Mage::helper('customreport')->__('Unable to send email.');
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customreport')->__('Unable to send email.'));
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        //$this->_redirect('*/*/');
        /*$mailTemplate = Mage::getModel('core/email_template');
        $emailvars = array();
        $emailvars['order'] = $order;
        $mailTemplate->setSenderName ("Priceguru");
        $mailTemplate->setSenderEmail ("info@priceguru.mu");
        $mailTemplate->setType('html');
        $mailTemplate->setTemplateSubject ('Priceguru: Credit Card Application');
        //$mailTemplate->addBcc ( $bcccustomeremailarray );
        $mailTemplate->sendTransactional ($template_id, "Priceguru", $order->getEmail(), $order->getCustomerName(), $emailvars);
        */
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    function _isAllowed()
    {
        return true;
    }

}