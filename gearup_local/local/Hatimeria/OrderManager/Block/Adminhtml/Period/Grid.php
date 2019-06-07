<?php

class Hatimeria_OrderManager_Block_Adminhtml_Period_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('period_grid');
        $this->setDefaultSort('date_from');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        if (!array_key_exists('dir', $params) || !array_key_exists('sort', $params)){
            $direction = 'desc';
            $sortOrder = 'date_from';
        } else {
            $direction = $this->getRequest()->getParam('dir');
            $sortOrder = $this->getRequest()->getParam('sort');
        }

        $collection = Mage::getModel('hordermanager/period')->getCollectionFormattedGrid($sortOrder, $direction);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('custom_period_id', array(
            'header'    => Mage::helper('hordermanager')->__('Period Id'),
            'align'     => 'left',
            'index'     => 'custom_period_id'
        ));

        $this->addColumn('date_from', array(
            'header'    => Mage::helper('hordermanager')->__('Begin of period'),
            'align'     => 'left',
            'index'     => 'date_from',
            'renderer' => 'hordermanager/adminhtml_period_grid_column_renderer_datefrom',
        ));

        $this->addColumn('date_to', array(
            'header'    => Mage::helper('hordermanager')->__('End of Period'),
            'align'     => 'left',
            'index'     => 'date_to',
            'renderer' => 'hordermanager/adminhtml_period_grid_column_renderer_dateto',
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
        return $this->getUrl('*/*/view', array('period_id' => $row->getId()));
    }
}