<?php

class Gearup_Countdown_Block_Adminhtml_Dailydeal_Grid extends  Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('gearup_countdown_dailydeal_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
     
    protected function _getCollectionClass()
    {
        return 'countdown/countdown_collection';
    }
     
    protected function _prepareCollection()
    {
        $current     = Mage::helper('countdown')->getCurrentTime('sql');
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $prodNameAttrId = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeId, 'name')->getAttributeId();
        $collection->getSelect()
        ->joinLeft(array('prod' => 'catalog_product_entity'), 'prod.entity_id = main_table.entity_id', array('sku'))
        ->joinLeft(array('cpev' => 'catalog_product_entity_varchar'),'cpev.entity_id=prod.entity_id AND cpev.attribute_id='.$prodNameAttrId.'', 
            array('name' => 'value'))
        //->where('main_table.expire_datetime_off>"' . $current . '"')
                ; 
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );
         
        $this->addColumn('name',
            array(
                'header'=> $this->__('Name'),
                'index' => 'name',
                'filter_index' => 'value',
                
            )
        );
        $this->addColumn('sku',
            array(
                'header'=> $this->__('Sku'),
                'index' => 'sku',
                'renderer' => 'gearup_countdown/adminhtml_dailydeal_renderer_sku',
            )
        );
        $this->addColumn('offer_price',
            array(
                'header'=> $this->__('Special Price'),
                'index' => 'offer_price',
                //'type'  => 'price',
            )
        );
        $this->addColumn('expire_datetime_on',
            array(
                'header'=> $this->__('Start Time'),
                'index' => 'expire_datetime_on',
                'type'   => 'datetime',
                'renderer' => 'gearup_countdown/adminhtml_dailydeal_renderer_expireon',
            )
        );
        $this->addColumn('expire_datetime_off',
            array(
                'header'=> $this->__('End Time'),
                'index' => 'expire_datetime_off',
                'type'   => 'datetime',
                'renderer' => 'gearup_countdown/adminhtml_dailydeal_renderer_expireoff',
            )
        );
        
        $this->addColumn('action',
                array(
                    'header'    => $this->__('Action'),
                    'width'     => '100px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'renderer'  => 'gearup_countdown/adminhtml_dailydeal_renderer_delete',
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            )); 
         
        return parent::_prepareColumns();
    }
     
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
}