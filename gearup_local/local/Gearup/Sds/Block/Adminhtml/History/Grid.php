<?php
class Gearup_Sds_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getModel('gearup_sds/history')->getCollection();
        $collection->addProductData();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /* Ticket 5492 - 
    -- Cost and CostValue data should be come from gearup_sds_history 
    -- if value does not availabel then it should be take from product attribute */
    protected function _prepareColumns()
    {
        $this->addColumn('create_date',
            array(
                'header'=> Mage::helper('catalog')->__('Record at'),
                'width' => '100px',
                'index' => 'create_date',
                'time'  => 'true',
                'type'  => 'datetime',
                'format'  => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
        ));

        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Product Name'),
                'index' => 'name',
                'filter'    => false,
                'sortable'  => false,
                'renderer' => 'gearup_sds/adminhtml_history_grid_column_renderer_name',
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('Sku'),
                'index' => 'sku',
        ));

        $this->addColumn('part_number',
            array(
                'header'=> Mage::helper('catalog')->__('Part number'),
                'index' => 'part_number',
        ));

        $this->addColumn('actions',
            array(
                'header'=> Mage::helper('catalog')->__('Action'),
                'index' => 'actions',
                'filter'    => false,
                'sortable'  => false,
                'renderer' => 'gearup_sds/adminhtml_history_grid_column_renderer_historyaction',
        ));

        $this->addColumn('order_id',
            array(
                'header'=> Mage::helper('catalog')->__('Order Number'),
                'index' => 'order_id',
        ));

        $this->addColumn('qty',
            array(
                'header'=> Mage::helper('catalog')->__('Qty'),
                'index' => 'qty',
        ));

        $this->addColumn('sds_qty',
            array(
                'header'=> Mage::helper('catalog')->__('SDS Qty'),
                'index' => 'sds_qty',
        ));

        $this->addColumn('ext_qty',
            array(
                'header'=> Mage::helper('catalog')->__('EXT Qty'),
                'index' => 'ext_qty',
        ));

        $this->addColumn('in_out',
            array(
                'header'=> Mage::helper('catalog')->__('In/Out'),
                'index' => 'in_out',
        ));

        $this->addColumn('sds_status',
            array(
                'header'=> Mage::helper('catalog')->__('SDS Status'),
                'index' => 'sds_status',
                'type'=>'options',
                'options' => array('1' => 'Yes', '0' => 'No')
        ));

        $store = Mage::app()->getStore();
        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'index' => 'cost',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'renderer'  => 'Gearup_Sds_Block_Adminhtml_History_Grid_Column_Renderer_Cost'
        ));

        $this->addColumn('cost_value',
            array(
                'header'=> Mage::helper('catalog')->__('Value'),
                'index' => 'cost_value',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'renderer'  => 'Gearup_Sds_Block_Adminhtml_History_Grid_Column_Renderer_Value'
        ));

        $this->addColumn('user',
            array(
                'header'=> Mage::helper('catalog')->__('User'),
                'index' => 'user',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('catalog')->__('CSV'));

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
