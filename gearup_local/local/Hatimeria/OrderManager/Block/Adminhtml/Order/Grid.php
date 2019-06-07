<?php

class Hatimeria_OrderManager_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('period_order_grid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('hordermanager/order')->getCollection();
        $collection->getCollectionWithCustomIds();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('hordermanager')->__('Order Id'),
            'align'     => 'left',
            'index'     => 'increment_id',
        ));

        $this->addColumn('custom_period_id', array(
            'header'    => Mage::helper('hordermanager')->__('Period Id'),
            'align'     => 'left',
            'index'     => 'custom_period_id'
        ));

        $this->addColumn('date_from', array(
            'header' => Mage::helper('hordermanager')->__('Date From'),
            'index' => 'date_from'
        ));

        $this->addColumn('date_to', array(
            'header' => Mage::helper('hordermanager')->__('Date To'),
            'index' => 'date_to'
        ));

        $this->addColumn('order_id', array(
            'index' => 'order_id',
            'column_css_class'=>'no-display',
            'header_css_class'=>'no-display'
        ));

        $this->addColumn('period_id', array(
            'index' => 'period_id',
            'column_css_class'=>'no-display',
            'header_css_class'=>'no-display'
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
        return $this->getUrl('*/*/add',
            array(
                'period_id' => $row->getPeriodId()
            )
        );
    }
}