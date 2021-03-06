<?php

class Hatimeria_MassAction_Block_Adminhtml_Sales_Order_Grid extends Hatimeria_MassAction_Block_Adminhtml_Sales_Order_Grid_Amasty_Pure
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->addItem('pending', array(
            'label'=> Mage::helper('hmassaction')->__('Pending'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'pending')),
        ));

        $this->getMassactionBlock()->addItem('pending_payment', array(
            'label'=> Mage::helper('hmassaction')->__('Pending Payment'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'pending_payment')),
        ));

        $this->getMassactionBlock()->addItem('processing', array(
            'label'=> Mage::helper('hmassaction')->__('Ordered'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'processing')),
        ));

//        $this->getMassactionBlock()->addItem('processing', array(
//            'label'=> Mage::helper('hmassaction')->__('Processing'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'processing')),
//        ));

//        $this->getMassactionBlock()->addItem('processing_reserved', array(
//            'label'=> Mage::helper('hmassaction')->__('Processing: Reserved'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'processing_reserved')),
//        ));

//        $this->getMassactionBlock()->addItem('processing_confirmed', array(
//            'label'=> Mage::helper('hmassaction')->__('Processing: Confirmed'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'processing_confirmed')),
//        ));

        $this->getMassactionBlock()->addItem('processing_confirmed', array(
            'label'=> Mage::helper('hmassaction')->__('Order processing'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'processing_confirmed')),
        ));

//        $this->getMassactionBlock()->addItem('complete', array(
//            'label'=> Mage::helper('hmassaction')->__('Complete: Shipped'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'complete')),
//        ));

        $this->getMassactionBlock()->addItem('complete', array(
            'label'=> Mage::helper('hmassaction')->__('Shipped'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'complete')),
        ));

//        $this->getMassactionBlock()->addItem('complete_delivered', array(
//            'label'=> Mage::helper('hmassaction')->__('Complete: Delivered'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'complete_delivered')),
//        ));

        $this->getMassactionBlock()->addItem('complete_delivered', array(
            'label'=> Mage::helper('hmassaction')->__('Delivered'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'complete_delivered')),
        ));

        $this->getMassactionBlock()->addItem('close', array(
            'label'=> Mage::helper('hmassaction')->__('Close'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=> true, 'action' => 'closed')),
        ));

        $this->getMassactionBlock()->addItem('payment_review', array(
            'label'=> Mage::helper('hmassaction')->__('Payment Review'),
            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=> true, 'action' => 'payment_review')),
        ));

//        $this->getMassactionBlock()->addItem('suspected_fraud', array(
//            'label'=> Mage::helper('hmassaction')->__('Suspected Fraud'),
//            'url'  => $this->getUrl('hmassaction/adminhtml_mass/mass', array('_current'=>true, 'action' => 'fraud')),
//        ));

        return;

    }
}