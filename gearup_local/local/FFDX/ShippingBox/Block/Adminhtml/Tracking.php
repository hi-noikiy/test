<?php

class FFDX_ShippingBox_Block_Adminhtml_Tracking extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'ffdxshippingbox';

    public function __construct()
    {
        $this->_controller = 'adminhtml_tracking';
        $this->_headerText = Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox Tracking')
            . '<br />'
            . 'Last refreshing: ' . Mage::getStoreConfig('ffdxshippingbox/lastRefresh/date');

        parent::__construct();
        $this->removeButton('add');
        $this->addButton('refresh', array(
            'label'     => Mage::helper('ffdxshippingbox')->__('Refresh'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/refresh') .'\')',
            'class'     => 'refresh',
        ));
        $this->addButton('check_one', array(
            'label'     => Mage::helper('ffdxshippingbox')->__('Check one'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/check_one') .'\')',
            'class'     => 'go'
        ));

        $this->addButton('load_tracks', array(
            'label'     => Mage::helper('ffdxshippingbox')->__('Load Tracks'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/load') .'\')',
            'class'     => 'go'
        ));

        $this->addButton('clean_tracks', array(
            'label'     => Mage::helper('ffdxshippingbox')->__('Clean Tracks'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('ffdxshippingbox/adminhtml_tracking/clean') .'\')',
            'class'     => 'go'
        ));
    }
}