<?php

class Ktpl_Repaircenter_Adminhtml_RepaircenterController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/repaircenter')
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
        $fileName = 'repaircenter.csv';
        $content = $this->getLayout()->createBlock('repaircenter/adminhtml_repaircenter_export')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'repaircenter.xml';
        $content = $this->getLayout()->createBlock('repaircenter/adminhtml_repaircenter_export')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function updateRowFieldsAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if($data = $this->getRequest()->getPost()) {
          
            if ($fieldId) {
                $model = Mage::getModel('repaircenter/repaircenter')->load($fieldId);
                $model->setData($data)->setId($fieldId);
                $model->save();
                $model = Mage::getModel('repaircenter/repaircenter')->load($fieldId);
                if($data['is_pickup'] || $data['dispatch']){
                    $datapickup = array();
                     $sku=explode('SKU:',$model->getProduct());
                        $order = Mage::getModel('sales/order')->load($model->getIncrementId(),'increment_id');
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
                    
                   
                    $pickup = Mage::getModel('customreport/salespickuporder')->getCollection()
                           ->addFieldToFilter('sku',$sku[1])
                           ->addFieldToFilter('order_id',$model->getIncrementId())
                           ->getFirstItem();
                    
                    $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $result =  $read->query("SELECT CURRENT_TIMESTAMP")->fetch();
                    if($data['is_pickup']){
                        if($pickup->getPickupId()){
                            $pickup->setPickup($data['is_pickup']) ;
                            $pickup->setStatus(1);
                            $pickup->setCreatedDate($result['CURRENT_TIMESTAMP']);
                            $pickup->setRepairId($fieldId.'_to_center') ;
                            $pickup->save();
                        }
                        else{
                                
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
                            $datapickup['repair_id'] = $fieldId.'_to_center';

                            $pickupmodel = Mage::getModel('customreport/salespickuporder');
                            $pickupmodel->setData($datapickup);
                            $pickupmodel->save();
                        }
                     } 
                    if($data['dispatch']){
                            $deliverymodel = Mage::getModel('customreport/salesdeliveryorder')->load($fieldId.'_Product_to_Center', 'repair_id');
                            $deliverymodel->setRepair_id($fieldId.'_Product_to_Center');
                            $deliverymodel->setAddress($model->getScAddress());
                            $deliverymodel->setLatitude($model->getScLatitude());
                            $deliverymodel->setLongitude($model->getScLongitude());
                        }else {
                            $deliverymodel = Mage::getModel('customreport/salesdeliveryorder')->load($fieldId.'_Collect_at_Customer', 'repair_id');
                            $deliverymodel->setRepair_id($fieldId.'_Collect_at_Customer');
                            $deliverymodel->setAddress($model->getCAddress());
                            $deliverymodel->setLatitude($model->getCLatitude());
                            $deliverymodel->setLongitude($model->getCLongitude());
                        } 
                        $deliverymodel->setOrder_id($order->getIncrementId());
                        $deliverymodel->setReal_order_id($order->getId()); 
                        $deliverymodel->setCustomer_name($order->getCustomerFirstname().' '.$order->getCustomerLastname());
                        $deliverymodel->setTelephone($order->getBillingAddress()->getData('telephone'));
                        $deliverymodel->setProduct_name($item->getData('name'));
                        $deliverymodel->setSku($itemsku);
                        $deliverymodel->setQty($qty);
                        $deliverymodel->setAttributes($custom_option);
                        $deliverymodel->setPayment_method(($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle());
                        $deliverymodel->setDelivery_comment($order->getCustomerNote());
                        $deliverymodel->setStatus(1);
                        $deliverymodel->setOrder_created_date(date('Y-m-d H:i:s'));

                        $deliverymodel->save();
                }    
               
            if($data['status']==2){
                
                $repairc= array();
                $rmodel=Mage::getModel('repaircenter/repaircustomer')->load($model->getRepairId(), 'repair_center_id');;
                $rmodel->setRepairCenterId($model['repair_id']);
                $rmodel->setCustomer($model['customer']);
                $rmodel->setProduct($model['product']);
                $rmodel->save(); 
            }
                $model->setData($data)->setId($fieldId);
                $model->save();
                
            }   
        }
    }
    
    public function createAction()
    {
        try{
            $fieldId = (int) $this->getRequest()->getParam('order_id');
            $sku= base64_decode($this->getRequest()->getParam('sku')); 
            $order =  Mage::getModel('sales/order')->load($fieldId);
            $repairdata = array();
            $repairdata['created_time'] = date('Y-m-d H:i:s');
            $repairdata['increment_id'] = $order->getIncrementId();
            $repairdata['pickup_address'] = $order->getBillingAddress()->getData('street');
            $repairdata['customer'] = $order->getBillingAddress()->getName().'<br /> T:'.
                    $order->getBillingAddress()->getData('telephone').'<br />E:'.$order->getCustomerEmail() ;
           
            $items = $order->getAllItems();
            foreach($items as $item) {
                if($item->getSku()==$sku){
                if($item->getData('product_options')) {
                   $opts = unserialize($item->getData('product_options'));
                   $custom_option = $opts['options'][0]['value']; 	
                } else {
                   $custom_option = "";
                }
                if($custom_option){$custom_option.='<br />';}
                $repairdata['product']  = $item->getData('name').'<br />'.$item->getProduct()->getNewSku().
                       '<br />'.$custom_option.'SKU:'.$item->getSku();
                $pickup = Mage::getModel('customreport/salespickuporder')->getCollection()
                           ->addFieldToFilter('sku',$item->getSku())
                           ->addFieldToFilter('order_id',$order->getIncrementId())
                           ->load();

                $repairdata['wholesaler'] = $pickup->getFirstItem()->getWholesaler_id(); 
                $repairdata['status'] = 1;

                   /* Insert data for repair order */
                   $repairmodel = Mage::getModel('repaircenter/repaircenter');
                   $repairmodel->setData($repairdata);
                   $repairmodel->save(); 
            }
            }  
        } catch (Mage_Core_Exception $e) {
            $result['error'] = true;
            $result['error_message'] = $e->getMessage();
        }
        if($result['error']) {
            Mage::getSingleton('adminhtml/session')->addError($result['error_message']);
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('repaircenter')->__('Repair Request created successfully.'));
        }
        $this->_redirect('adminhtml/sales_order/view',array('order_id'=>$fieldId));
    }        

    public function updateServiceaddressAction() {
        $serviceid = $this->getRequest()->getParam('serviceid');
        $fieldId = (int) $this->getRequest()->getParam('id');

        $whole = Mage::getModel('repaircenter/servicecenter')->load($serviceid);
        $pickupmodel = Mage::getModel('repaircenter/repaircenter')->load($fieldId);
        $pickupmodel->setScId($serviceid);    
        $pickupmodel->setSc_address($whole->getServiceAddress());
        $pickupmodel->setSc_latitude($whole->getServiceLatitude());
        $pickupmodel->setSc_longitude($whole->getServiceLongitude());
        $pickupmodel->save();
        
        $res = array(0 => $whole->getServiceAddress(),1 => $whole->getServiceLatitude(),2 => $whole->getServiceLongitude());
        echo json_encode($res);
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