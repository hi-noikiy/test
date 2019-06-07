<?php

class Gearup_Shippingffdx_Block_Adminhtml_Tracking extends FFDX_ShippingBox_Block_Adminhtml_Tracking
{
    protected $_blockGroup = 'ffdxshippingbox';

    public function __construct()
    {
        parent::__construct();
        if (!Mage::app()->getRequest()->getParam('ofd')) {
            $this->addButton('ofd_missing', array(
                'label'     => Mage::helper('ffdxshippingbox')->__('OFD missing'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/index', array('ofd'=>1)) .'\')',
                'class'     => 'refresh'
            ));
        } else {
            $this->addButton('back', array(
                'label'     => Mage::helper('ffdxshippingbox')->__('Back'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/index') .'\')',
                'class'     => 'back'
            ));
        }

        if (!Mage::app()->getRequest()->getParam('ofd')) {
            $this->_headerText = Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox Tracking')
                . '<br />'
                . 'Last refreshing: ' . Mage::getStoreConfig('ffdxshippingbox/lastRefresh/date');
        } else {
            $this->_headerText = Mage::helper('ffdxshippingbox')->__('OFD Missing');
        }
        $this->addButton('report', array(
            'label'     => Mage::helper('ffdxshippingbox')->__('Report'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('adminhtml/reportffdx/index') .'\')',
            'class'     => 'scalable'
        ));
    }
}