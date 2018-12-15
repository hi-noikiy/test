<?php

class Ktpl_Customreport_Adminhtml_PoorderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/sales_po')
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
        $fileName = 'poorder.csv';
        $content = $this->getLayout()->createBlock('customreport/adminhtml_poorder_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'poorder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_poorder_grid')
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
                $model = Mage::getModel('customreport/poorder');
                $model->setData($data)->setId($fieldId);
                $model->save();
               
                $upmodel = Mage::getModel('customreport/poorder')->load($fieldId);
                $cid = $upmodel->getOrderId(); 
                $cimmodel = Mage::getModel('customreport/salescimorder')->load($cid,'order_id');
                $order = Mage::getModel('sales/order')->load($upmodel->getRealOrderId());
                
                if($data['delivery_comment'] != "") {
                    $order->addStatusHistoryComment($data['delivery_comment']);
                    $order->save();
                }
                
                if($data['status'] == '3') {
                    $deliverymodel = Mage::getModel('customreport/salespickuporder')->load($upmodel->getPoId(), 'po_id');
                    $deliverymodel->setOrderId($upmodel->getOrderId());
                    $deliverymodel->setRealOrderId($upmodel->getRealOrderId());
                    $deliverymodel->setPoId($upmodel->getPoId());
                    $deliverymodel->setRegion($upmodel->getRegion());
                    $deliverymodel->setCustomerName($upmodel->getCustomerName());
                    $deliverymodel->setTelephone($upmodel->getTelephone());
                    $deliverymodel->setAddress($upmodel->getAddress());
                    $deliverymodel->setProductName($upmodel->getProductName());
                    $deliverymodel->setSku($upmodel->getSku());
                    $deliverymodel->setQty($upmodel->getQty());
                    $deliverymodel->setAttributes($upmodel->getAttributes());
                    $deliverymodel->setPaymentMethod($upmodel->getPaymentMethod());
                    $deliverymodel->setDeposit($cimmodel->getDeposit());
                    //$deliverymodel->setDeposit($upmodel->getDeposit());
                    $deliverymodel->setPoComment($upmodel->getPoComment());
                    $deliverymodel->setStatus(1);
                    $deliverymodel->setPickupDone(2);
                    $deliverymodel->setWholesalePrice($upmodel->getWholesalePrice());
                    $deliverymodel->setRetailPrice($upmodel->getRetailPrice());
                    $deliverymodel->setMarkup($upmodel->getMarkup());
                    $deliverymodel->setWholesalerId($upmodel->getWholesalerId());
                    $deliverymodel->setPickupAddress($upmodel->getPickupAddress());
                    $deliverymodel->setOrderCreatedDate(date('Y-m-d H:i:s'));
                    $deliverymodel->save();
                    
                    $start_date = new DateTime($upmodel->getOrderCreatedDate());
                    $since_start = $start_date->diff(new DateTime());
                    $time = '';
                    if($since_start->days > 1){
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
        $pickupmodel = Mage::getModel('customreport/poorder')->load($fieldId);
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
            $pickupmodel = Mage::getModel('customreport/poorder')->load($fieldId);
            $retail_price = $pickupmodel->getRetailPrice();
            $markup = (($retail_price - $wholesaleprice) * 100) / $retail_price;
            $markup = round($markup);
            $pickupmodel->setWholesalePrice($wholesaleprice);
            $pickupmodel->setMarkup($markup);
            $pickupmodel->save();
            echo $markup;
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