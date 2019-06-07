<?php

class FFDX_ShippingBox_Block_Adminhtml_Tracking_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tracking_id');
        $this->setDefaultSort('tracking_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /**
         * columns of table track.ffdxshippingbox_tracking
         */
        $this->addColumn('tracking_id', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('tracking_id'),
            'align'     => 'left',
            'index'     => 'tracking_id'
        ));

        $this->addColumn('order_id', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('increment_id'),
            'align'     => 'left',
            'index'     => 'increment_id'
        ));

        $this->addColumn('tracking_number', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('tracking_number'),
            'align'     => 'left',
            'index'     => 'tracking_number'
        ));

        $this->addColumn('checked', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Status'),
            'align'     => 'left',
            'width'     => '20px',
            'index'     => 'checked'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/history', array('tracking_id' => $row->getId()));
    }
}