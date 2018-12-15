<?php

class Ktpl_Guestabandoned_Adminhtml_GuestabandonedController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() 
    {
        $this->loadLayout()->_setActiveMenu('customer/guestabandoned');
        
        if(!Mage::helper('guestabandoned')->isEnabled())
        {
            $this->_redirect('adminhtml/dashboard/index');
        }
        return $this;
    }
    
    public function refreshAction() {
        Mage::getModel('guestabandoned/observer')->refreshData();
        $this->_redirect('*/*/index');
    }
    
    public function gridAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }
    
    public function indexAction() 
    {
        $this->_title($this->__('Guest Abandoned Cart'));
        $this->_initAction()->renderLayout();
    }

    public function viewAction() 
    {
        $this->_title($this->__('Guest Abandoned Cart'));
        $this->_initAction()->renderLayout();
    }

    public function exportCsvAction() 
    {
        $fileName = 'guestabandoned.csv';
        $content = $this->getLayout()->createBlock('guestabandoned/adminhtml_guestabandoned_grid')
                ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'guestabandoned.xml';
        $content = $this->getLayout()->createBlock('guestabandoned/adminhtml_guestabandoned_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function massDeleteAction() 
    {
        $ids = $this->getRequest()->getParam('entity_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Quote(s)'));
        } else {
            try {
                $quotes = Mage::getModel('sales/quote')->getCollection();
                $quotes->addFieldToFilter('entity_id', array($ids));
                $quotes->walk('delete');
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($ids)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction(){
        $quoteIds = $this->getRequest()->getParam('entity_id');
        if(!is_array($quoteIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select quote(s)'));
        } else {
            try {
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $write->beginTransaction();
                foreach ($quoteIds as $quoteId) {
                    $updateQue = 'UPDATE `sales_flat_quote` SET status='.$this->getRequest()->getParam('status').' WHERE entity_id='.$quoteId;
                    $write->query($updateQue);
                }
                $write->commit();
               Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($quoteIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index'); 
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
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

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/guestabandoned');
    }

}
