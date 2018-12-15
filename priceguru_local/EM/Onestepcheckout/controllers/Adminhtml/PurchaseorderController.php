<?php

class EM_Onestepcheckout_Adminhtml_PurchaseorderController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales/purchaseorder')
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

    public function massDeleteAction() {
        $pickupIds = $this->getRequest()->getParam('pickuporder');
        if(!is_array($pickupIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($pickupIds as $pickupId) {
                    $pickuporder = Mage::getModel('onestepcheckout/salespickuporder')->load($pickupId);
                    $pickuporder->setPoCreated(0);
                    $pickuporder->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($pickupIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'purchaseorder.csv';
        $content = $this->getLayout()->createBlock('onestepcheckout/adminhtml_purchaseorder_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'pickuporder.xml';
        $content = $this->getLayout()->createBlock('ordercustomer/adminhtml_purchaseorder_grid ')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
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