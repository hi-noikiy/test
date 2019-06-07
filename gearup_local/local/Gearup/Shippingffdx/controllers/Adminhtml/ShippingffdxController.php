<?php

class Gearup_Shippingffdx_Adminhtml_ShippingffdxController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ffdxshippingbox/gearup_shippingffdx');
    }
    public function changeStatusAction() {
        $trackingId = Mage::app()->getRequest()->getParam('tracking_id');
        $track = Mage::getModel('ffdxshippingbox/tracking')->load($trackingId);
//        $track->setChecked(1);
//        $track->save();
        $trackCollection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $trackCollection->addFieldToFilter('order_id', array('eq' => $track->getOrderId()));
        $trackCollection->addFieldToFilter('checked', array('eq' => 0));
        if ($trackCollection->getSize()) {
            foreach ($trackCollection as $trackrelated) {
                $trackrelated->setChecked(1);
                $trackrelated->save();
            }
        }
        $this->_getSession()->addSuccess(
                $this->__('Status is changed')
        );
        $this->_redirect('ffdxshippingbox/adminhtml_tracking/index');
    }

    public function exportToCSVAction() {
        $request = $this->getRequest();
        $ids = $request->getParam('order_ids');
        $content = "Shipment #,Order #,AWB #\n";
        switch ($request->getParam('massaction_prepare_key')) {
            case 'shipment_ids':
                $ids = $request->getParam('shipment_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                            ->addFieldToFilter('entity_id', array('in' => $ids));
                }
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                            ->setOrderFilter(array('in' => $ids));
                }
                break;           

        }

        if ($shipments) {
            foreach ($shipments as $index) {
                 $order = Mage::getModel('sales/order')->load($index->getData('order_id'));
                 $shipmentTracks =$index->getAllTracks();
                 $trackNumber = [];
                 foreach($shipmentTracks  as $index2){
                     $trackNumber[] = $index2->getData('track_number');
                 }

                 $content .= implode(',', [$index->getData('increment_id'),$order->getData('increment_id'),implode(',',$trackNumber)])."\n";
            }
        }

        $this->_prepareDownloadResponse('Shipments-' . date('Y-m-d') . '.csv', $content, 'text/csv');
    }

}
