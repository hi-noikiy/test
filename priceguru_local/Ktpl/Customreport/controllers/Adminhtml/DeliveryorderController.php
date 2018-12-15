<?php

class Ktpl_Customreport_Adminhtml_DeliveryorderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/sales_delivery')
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
        $fileName = 'deliveryporder.csv';
        $content = $this->getLayout()->createBlock('customreport/adminhtml_deliveryorder_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'deliveryporder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_deliveryorder_grid')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function updateRowFieldsAction()
    {
        $fieldId = (int) $this->getRequest()->getParam('id');
        if($data = $this->getRequest()->getPost()) {
            
            if($data['deposit'] == "") { 
                $data['deposit'] == NULL; 
            }
            
            if ($fieldId) {
                $model = Mage::getModel('customreport/salesdeliveryorder');
                $model->setData($data)->setId($fieldId);
                $model->save();

                $upmodel = Mage::getModel('customreport/salesdeliveryorder')->load($fieldId);
                if($data['status'] == '3') {
                    $todaydate = date('Y-m-d');
                    $purchase = date('Y-m-d', strtotime($upmodel->getData('order_created_date')));
                    //$diff = round(abs(strtotime($todaydate) - strtotime($purchase))/86400);
                    
                    $start_ts = strtotime($todaydate);
                    $end_ts = strtotime($purchase);
                    $diff = $start_ts - $end_ts;
                    $diffdays = round($diff / 86400);
                    if($diffdays == 1) {
                        $diffdays = $diffdays." day";
                    } elseif($diffdays > 1) {
                        $diffdays = $diffdays." days";
                    } else {
                        $diffdays = '0 days';
                    }
                    $upmodel->setDeliveryTime($diffdays);
                    $upmodel->save();
                    //echo $diffdays;
                     
                    $order = Mage::getModel('sales/order')->loadByIncrementId($upmodel->getOrder_id());
                    $start =  new DateTime($order->getCreated_at());
                    $start_date = new DateTime($upmodel->getOrderCreatedDate());
                    $since_start = $start_date->diff(new DateTime());
                    $since = $start->diff(new DateTime());
                    $time = ''; $flead='';
                    if($since_start->days > 0){
                        $time .= $since_start->days.' Days : ';
                    }
                    $time .= $since_start->h.' Hours : '.$since_start->i .' Minutes' ;
                    if($since->days > 0){
                        $flead .= $since->days.' Days : ';
                    }
                    $flead .= $since->h.' Hours : '.$since->i .' Minutes' ;
                    $upmodel->setLeadtime($time);
                    $upmodel->setFullLeadtime($flead);
                    $upmodel->save();
                    $res = array(0 => $time,1 => $flead);
                    echo json_encode($res);
                } /*else {
                    $res = array(0 => $upmodel->getLeadtime(), 1 => $upmodel->getFullLeadtime());
                    //echo $upmodel->getData('delivery_time');
                } */
                
            }
        }
    }

    public function updateCommentAction()
    {
         $fieldId = (int) $this->getRequest()->getParam('id');
        if($data = $this->getRequest()->getPost()) {
             if($data['delivery_comment'] != "") {
                    $order = Mage::getModel('sales/order')->load($data['orderid']);
                    $order->addStatusHistoryComment($data['delivery_comment']);
                    $order->save();
                    echo $data['delivery_comment']; 
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