<?php

class Gearup_Shippingffdx_Adminhtml_ReportffdxController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function filterffdxAction() {
        $desti   = Mage::app()->getRequest()->getParam('desti');
        if (!$desti) {
            $this->_getSession()->addError(
                $this->__('Please select Destination type')
            );
            $this->_redirect('*/*/index');
        }
        $from   = Mage::app()->getRequest()->getParam('invfrom');
        $to     = Mage::app()->getRequest()->getParam('invto');
        $this->_redirect('*/*/index', array('from'=>base64_encode($from), 'to'=>base64_encode($to), 'desti'=>base64_encode($desti)));
    }

    public function compareAction()
    {
        $data = $_FILES['comparefile'];
        $from = Mage::app()->getRequest()->getParam('shippingfrom');
        $to = Mage::app()->getRequest()->getParam('shippingto');
        $desti = Mage::app()->getRequest()->getParam('shippingdesti');
        $path = Mage::getBaseDir() . DS . 'media/dxbs/shippingcompare';
        if (!Mage::helper('gearup_sds')->checkFileType($data['type'])) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('File upload not allow'));
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
            $this->_redirect('*/*/result', array('file'=>base64_encode($data['name']), 'from'=>$from, 'to'=>$to, 'desti'=>$desti));
        }
    }

    public function resultAction()
    {
        $path = Mage::getBaseDir() . DS . 'media/dxbs/shippingcompare/';
        if (!Mage::app()->getRequest()->getParam('file') || !file_exists($path.base64_decode(Mage::app()->getRequest()->getParam('file')))) {
            $this->_redirect('*/*/index');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function changestatusAction()
    {
        try {
            $tracks = explode(',', $this->getRequest()->getParam('tracks'));
            $file = $this->getRequest()->getParam('file');
            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            $desti = $this->getRequest()->getParam('desti');
            $report = array();
            foreach ($tracks as $track) {
                if ($track) {
                    $trackM = Mage::getModel('ffdxshippingbox/tracking')->load($track);
                    $order = Mage::getModel('sales/order')->load($trackM->getOrderId());
                    $trackCollection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
                    $trackCollection->addFieldToFilter('order_id', array('eq'=>$trackM->getOrderId()));
                    $trackCollection->addFieldToFilter('checked', array('eq'=>0));
                    if ($trackCollection->getSize()) {
                        foreach ($trackCollection as $trackrelated) {
                            $trackrelated->setChecked(1);
                            $trackrelated->save();
                            Mage::helper('gearup_shippingffdx')->recordHistory($trackrelated->getIncrementId(), 'Changed FFDX Track Checked from No to Yes');
                            $report[] = array(
                                'order'  => $order->getIncrementId(),
                                'track'  => $trackrelated->getTrackingNumber(),
                                'weight'  => round($order->getWeight(), 2),
                                'shippingamount'  => round($order->getShippingAmount(), 2),
                                'date'   => $order->getCreatedAt()
                            );
                        }
                    }

                }
            }
            Mage::getSingleton('core/session')->setTrackCReport($report);
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/reportffdx/result', array('file'=>$file, 'from'=>$from, 'to'=>$to, 'desti'=>$desti)), 'status' => 1));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'ffdxreporterror.log');
        }

    }

    public function exporttrackcAction() {
        Mage::getModel('gearup_shippingffdx/history')->downloadLastChange();
    }
}