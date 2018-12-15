<?php

class Ktpl_Repaircenter_Adminhtml_RepaircustomerController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/repaircustomer')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }
   
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'repaircustomer.csv';
        $content = $this->getLayout()->createBlock('repaircenter/adminhtml_repaircustomer_export')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'repaircustomer.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_repaircustomer_export')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function updateRowFieldsAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if($data = $this->getRequest()->getPost()) {
           
            if ($fieldId) {
                $model = Mage::getModel('repaircenter/repaircustomer')->load($fieldId);
                $rmodel = Mage::getModel('repaircenter/repaircenter')->load($model->getRepairCenterId());
                if($data['is_pickup']){
                    $sku=explode('SKU:',$model->getProduct());
                    $pickup = Mage::getModel('customreport/salespickuporder')->load($model->getRepairCenterId().'_to_customer','repair_id');
                          
                    
                   if($pickup->getPickupId()){
                        $pickup->setPickup($data['is_pickup']) ;
                       // $pickup->setRepairId($fieldId) ;
                        $pickup->save();
                    }
                    else{  
                        $datapickup = array();
                        $order = Mage::getModel('sales/order')->load($rmodel->getIncrementId(),'increment_id');
                        $items = $order->getAllItems();

                        foreach($items as $item) {
                            if($item->getSku()==$sku[1]){
                                if($item->getData('product_options')) {
                                        $opts = unserialize($item->getData('product_options'));
                                        $custom_option = $opts['options'][0]['value']; 	
                                } else {
                                        $custom_option = "";
                                }
                                $itemprice=$item->getPrice();
                                $itemsku=$item->getSku();
                                $qty=round($item->getQtyOrdered());
                            }    
                        }        
                        $datapickup['order_id'] = $order->getIncrementId();
                        $datapickup['real_order_id'] = $order->getId(); 
                        $datapickup['payment_method'] = ($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle();
                        $datapickup['order_created_date'] = date('Y-m-d H:i:s');
                        $datapickup['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
                        $datapickup['telephone'] = $order->getBillingAddress()->getData('telephone');
                        $datapickup['address'] = $order->getBillingAddress()->getData('street');
                        $datapickup['delivery_comment'] = $order->getCustomerNote();
                        $datapickup['product_name'] = $item->getData('name');
                        $datapickup['attributes'] = $custom_option;
                        $datapickup['sku'] = $itemsku;
                        $datapickup['retail_price'] = $itemprice;
                        $datapickup['qty'] = $qty;
                        $datapickup['status'] = 1;
                        $datapickup['pickup'] = $data['is_pickup'];
                        $datapickup['repair_id'] = $rmodel->getRepairId().'_to_customer';
                
                        $pickupmodel = Mage::getModel('customreport/salespickuporder');
                        $pickupmodel->setData($datapickup);
                        $pickupmodel->save();
                    }
                }
                $model->setData($data)->setId($fieldId);
                $model->save();

                $upmodel = Mage::getModel('repaircenter/repaircustomer')->load($fieldId);
                if($data['dispatch_date'] && $data['collect_date'] ) {
                 //  echo 'ash'; exit;
                    $start_ts = strtotime($data['collect_date']);
                    $end_ts = strtotime($data['dispatch_date']);
                    $diff = $start_ts - $end_ts;
                    $diffdays = round($diff / 86400);
                    if($diffdays == 1) {
                        $diffdays = $diffdays." day";
                    } elseif($diffdays > 1) {
                        $diffdays = $diffdays." days";
                    } else {
                        $diffdays = '0 days';
                    }
                    $upmodel->setLeadtime($diffdays);
                    $upmodel->save();
                    echo $diffdays;
                } else {
                    echo $upmodel->getData('leadtime');
                }
            }
        }
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