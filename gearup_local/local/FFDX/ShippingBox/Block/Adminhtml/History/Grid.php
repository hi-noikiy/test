<?php

class FFDX_ShippingBox_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('shipment_id');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $tracking = Mage::registry('current_tracking');
        $collection = Mage::getModel('ffdxshippingbox/history')->getHistoryGridCollection($tracking);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /**
         * columns of table track.ffdxshippingbox_tracking
         */

        $activity = Mage::getModel('ffdxshippingbox/source_event_code');

        $this->addColumn('tracking_id', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('tracking_id'),
            'align'     => 'left',
            'index'     => 'tracking_id'
        ));

//        $this->addColumn('activity', array(
//            'header'    => Mage::helper('ffdxshippingbox')->__('activity'),
//            'align'     => 'left',
//            'index'     => 'activity',
//            'type'      => 'options',
//            'options'   => $activity->getMap()
//        ));

        $this->addColumn('event', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('activity'),
            'align'     => 'left',
            'index'     => 'event',
            'type'      => 'options',            
            'options'   => $activity->getNewMap()            
        ));
        
        
//        $this->addColumn('location', array(
//            'header'    => Mage::helper('ffdxshippingbox')->__('Location'),
//            'align'     => 'left',
//            'index'     => 'location'
//        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('ffdxshippingbox')->__('Created at'),
            'align'     => 'left',
            'index'     => 'created_at'
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
        return $this->getUrl('*/*/view', array('shipment_id' => $row->getId()));
    }
}