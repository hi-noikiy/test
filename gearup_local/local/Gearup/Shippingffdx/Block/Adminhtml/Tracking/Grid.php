<?php

class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Grid extends FFDX_ShippingBox_Block_Adminhtml_Tracking_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $defaultFilters = array();
        $sessionParamName = $this->getId() . $this->getVarNameFilter();
        Mage::getSingleton('adminhtml/session')->setData($sessionParamName, '');
        $this->setDefaultFilter($defaultFilters);
    }

    public function setCollection($collection)
    {
        $tblShipment = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
        if (Mage::app()->getRequest()->getParam('ofd')) {
            $collection->addFieldToFilter('checked', array('eq'=>0));
            $ids = array();
            foreach ($collection as $track) {
                $models = Mage::getModel('ffdxshippingbox/history')->getCollection();
                $models->addFieldToFilter('tracking_id', array('eq'=>$track->getTrackingId()));
                $models->addFieldToFilter('event', array('like'=>'WC'));
                if ($models->getSize()) {
                    continue;
                }
                $ids[] = $track->getTrackingId();
            }
            $tracks = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
            $tracks->addFieldToFilter('tracking_id', array('in'=>$ids));
            parent::setCollection($tracks);
        } else {
            parent::setCollection($collection);
        }

    }

    protected function _prepareColumns()
    {
        /*$this->addColumn('tracking_id', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('tracking_id'),
            'align'     => 'left',
            'index'     => 'tracking_id'
        ));*/

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Shipped'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'created_at',
            'type'      => 'datetime'
        ));

        $this->addColumn('destination', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Destination'),
            'align'     => 'center',
            'width'     => '170px',
            'index'     => 'order_id',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Destination',
        ));

        $this->addColumn('order_id', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Order Number'),
            'width'     => '100px',
            'align'     => 'left',
            'index'     => 'increment_id',
            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Increment',
        ));

        $this->addColumn('tracking_number', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Tracking Number'),
            'align'     => 'left',
            'index'     => 'tracking_number'
        ));

        $this->addColumn('reference_number', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Reference'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'tracking_number',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Reference',
        ));

        $this->addColumn('checked', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Status'),
            'align'     => 'left',
            'width'     => '110px',
            'index'     => 'checked',
            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Status',
        ));

        $this->addColumn('external', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('External'),
            'align'     => 'left',
            'width'     => '50px',
            'index'     => 'checked',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_External',
        ));

        return $this;
    }
}