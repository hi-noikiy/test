<?php


class Gearup_Sds_Adminhtml_Sds_StockcompareController extends Gearup_Sds_Controller_Adminhtml_Sds
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function compareAction()
    {
        $data = $_FILES['comparefile'];
        $path = Mage::getBaseDir() . DS . 'media/dxbs/compare';
        if (!Mage::helper('gearup_sds')->checkFileType($data['type'])) {
            $this->_getSession()->addError(Mage::helper('gearup_sds')->__('File upload not allow'));
            $this->_redirect('*/*/index');
            return false;
        }
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
        if (file_exists($path.'/'.$data['name'])) {
            unlink($path.'/'.$data['name']);
        }
        if (!file_exists($path.'/'.$data['name'])) {
            move_uploaded_file($data['tmp_name'], $path.'/'.$data['name']);
            $this->_redirect('*/*/result', array('file'=>base64_encode($data['name'])));
        }
    }

    public function resultAction()
    {
        $path = Mage::getBaseDir() . DS . 'media/dxbs/compare/';
        if (!Mage::app()->getRequest()->getParam('file') || !file_exists($path.base64_decode(Mage::app()->getRequest()->getParam('file')))) {
            $this->_redirect('*/*/index');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}