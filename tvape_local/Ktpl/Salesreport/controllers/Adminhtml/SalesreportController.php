<?php

class Ktpl_Salesreport_Adminhtml_SalesreportController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/salesreport');
    }

    public function indexAction()
    {
        if(isset($_POST['start_date'])){
            Mage::getSingleton('core/session')->setMyCustomData($_POST);
        }
        $this->loadLayout()
            ->_setActiveMenu('report/salesreport')
            ->renderLayout();
    }
    
    public function gridAction()
     {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('salesreport/adminhtml_salesdata_grid')->toHtml()
        );
     }
    
    public function exportOrderedCsvAction() 
    {
        $fileName = 'salesreport.csv';
        $content = $this->getLayout()->createBlock('salesreport/adminhtml_salesdata_grid')
                ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportOrderedXmlAction()
    {
        $fileName = 'salesreport.xml';
        $content = $this->getLayout()->createBlock('salesreport/adminhtml_salesdata_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
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
}