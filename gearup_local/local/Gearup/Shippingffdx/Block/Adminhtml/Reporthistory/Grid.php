<?php
class Gearup_Shippingffdx_Block_Adminhtml_Reporthistory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('historyGrid');
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setRowClickCallback(null);
        $this->setDefaultLimit(200);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('gearup_shippingffdx/history')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date',
            array(
                'header'=> Mage::helper('catalog')->__('Record at'),
                'width' => '100px',
                'index' => 'create_date',
                'time'     => 'true',
                'type'  => 'date',
                'format'  => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
        ));

        $this->addColumn('track_id',
            array(
                'header'=> Mage::helper('catalog')->__('Track Number'),
                'width' => '200px',
                'index' => 'track_id',
        ));

        $this->addColumn('actions',
            array(
                'header'=> Mage::helper('catalog')->__('Action'),
                'index' => 'actions',
                'filter'    => false,
                'sortable'  => false,
                'renderer' => 'gearup_autoinvoice/adminhtml_history_grid_column_renderer_historyaction',
        ));

        $this->addColumn('record_by',
            array(
                'header'=> Mage::helper('catalog')->__('User'),
                'width' => '200px',
                'index' => 'record_by',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }
}
