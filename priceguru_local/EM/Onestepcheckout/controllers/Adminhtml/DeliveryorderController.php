<?php

class EM_Onestepcheckout_Adminhtml_DeliveryorderController extends Mage_Adminhtml_Controller_Action {

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
        $this->getResponse()->setBody($this->getLayout()->createBlock('onestepcheckout/adminhtml_pickuporder/grid')->toHtml()); 
    }*/

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'deliveryporder.csv';
        $content = $this->getLayout()->createBlock('onestepcheckout/adminhtml_deliveryorder_export')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'deliveryporder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_deliveryorder_export')
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
                $model = Mage::getModel('onestepcheckout/salesdeliveryorder');
                $model->setData($data)->setId($fieldId);
                $model->save();

                $upmodel = Mage::getModel('onestepcheckout/salesdeliveryorder')->load($fieldId);
                if($data['status'] == '3' && ($upmodel->getData('delivery_time') == "" || $upmodel->getData('delivery_time') == NULL)) {
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
                    echo $diffdays;
                } else {
                    echo $upmodel->getData('delivery_time');
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