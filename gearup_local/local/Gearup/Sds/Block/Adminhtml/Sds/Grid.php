<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
        $this->setRowDblClickCallback('dblClick');
        $this->setRowClickCallback(null);
        $this->setDefaultLimit(200);
        if (Mage::app()->getRequest()->getParam('reset')) {
            $defaultFilters = array();
            $sessionParamName = $this->getId() . $this->getVarNameFilter();
            Mage::getSingleton('adminhtml/session')->setData($sessionParamName, '');
            $this->setDefaultFilter($defaultFilters);
        }
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $this->saveStatistic();
        $collection = $this->getDataCollection();
        $collection->addFieldToFilter('dxbs', 1);
        if ($this->getRequest()->getParam('order_filter')) {
            $lowids = array();
            foreach ($collection as $obj) {
                $model = Mage::getModel('catalog/product')->load($obj->getEntityId());
                if ($model->getLowStock()) {
                    if ($model->getLowStock() != $obj->getInbound() && (($obj->getInbound() + $model->getStockItem()->getQty()) < $model->getLowStock() || ($model->getSameDayShipping() == 0 && $obj->getInbound() < $model->getLowStock()))) {
                        $lowids[] = $obj->getEntityId();
                    }
                }
            }
            $productCollection = $this->getDataCollection();

            $productCollection->addFieldToFilter('entity_id', array('in'=>$lowids));
            $collection = $productCollection;
        }

        if ($this->getRequest()->getParam('inbound_filter')) {
            $collection->getSelect()->where("last.inbound != ''");
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function saveStatistic()
    {
        $collection = $this->getDataCollection();
        $collection->addFieldToFilter('dxbs', 1);
        if ($this->getRequest()->getParam('order_filter')) {
            $lowids = array();
            foreach ($collection as $obj) {
                $model = Mage::getModel('catalog/product')->load($obj->getEntityId());
                if ($model->getLowStock()) {
                    if ($model->getLowStock() != $obj->getInbound() && (($obj->getInbound() + $model->getStockItem()->getQty()) < $model->getLowStock() || ($model->getSameDayShipping() == 0 && $obj->getInbound() < $model->getLowStock()))) {
                        $lowids[] = $obj->getEntityId();
                    }
                }
            }
            $productCollection = $this->getDataCollection();

            $productCollection->addFieldToFilter('entity_id', array('in'=>$lowids));
            $collection = $productCollection;
        }

        if ($this->getRequest()->getParam('inbound_filter')) {
            $collection->getSelect()->where("last.inbound != ''");
        }

        $ids = array();
        foreach ($collection as $product) {
            $ids[] = $product->getEntityId();
        }
        Mage::getSingleton('core/session')->unsDxbsCollection();
        Mage::getSingleton('core/session')->setDxbsCollection($ids);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '160px',
                'index' => 'sku',
        ));

        $this->addColumn('part_nr',
            array(
                'header'=> Mage::helper('catalog')->__('Part number'),
                'width' => '185px',
                'index' => 'part_nr',
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_partnumber',
        ));

        if(!$this->getRequest()->getParam('storage_filter')){
            $this->addColumn('manufacturer',
                array(
                    'header'=> Mage::helper('catalog')->__('Manufacturer'),
                    'width' => '140px',
                    'index' => 'manufacturer',
                    'type'  => 'options',
                    'options'   => $this->_getAttributeOptions('manufacturer', 1),
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_manufacturer',
            ));
        }
        $store = $this->_getStore();
        if ($this->getRequest()->getParam('inbound_filter')) {
            $this->addColumn('price',
                array(
                    'header'=> Mage::helper('catalog')->__('Price'),
                    'type'  => 'price',
                    'width' => '60px',
                    'align' => 'right',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'price',
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_price',
            ));
        } else if(!$this->getRequest()->getParam('storage_filter')){
            $this->addColumn('price',
                array(
                    'header'=> Mage::helper('catalog')->__('Price'),
                    'type'  => 'price',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'price',
            ));
        }

        if(!$this->getRequest()->getParam('storage_filter')){
            $this->addColumn('special_price',
                array(
                    'header'=> Mage::helper('catalog')->__('Special Price'),
                    'type'  => 'price',
                    'width' => '60px',
                    'align' => 'right',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'special_price',
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_specialprice',
            ));
        }

        /* Ticket5492 - DXB Storage Manager - Report In/Out Added Cost field into dxb grid */
        if ($this->getRequest()->getParam('inbound_filter')){
            $this->addColumn('cost',
                array(
                    'header'=> Mage::helper('catalog')->__('Cost'),
                    'type'  => 'price',
                    'width' => '60px',
                    'align' => 'right',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'cost',
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_cost',
            ));
        }else{
            $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'cost',
            ));
        }

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            
            if ($this->getRequest()->getParam('inbound_filter')){
                $this->addColumn('qty',
                    array(
                        'header'=> Mage::helper('catalog')->__('Stock Qty'),
                        'width' => '100px',
                        'type'  => 'number',
                        'align' => 'right',
                        'index' => 'qty',
                        'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_inlineqty',
                ));
            } else if ($this->getRequest()->getParam('storage_filter')){    
                $this->addColumn('qty',
                    array(
                        'header'=> Mage::helper('catalog')->__('Stock Qty'),
                        'width' => '100px',
                        'type'  => 'number',
                         'align' => 'right',
                        'index' => 'qty',
                ));
            }else{
                $this->addColumn('qty',
                    array(
                        'header'=> Mage::helper('catalog')->__('Stock Qty'),
                        'width' => '100px',
                        'type'  => 'number',
                         'align' => 'right',
                        'index' => 'qty',
                        'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_inline',
                ));
            }

            if (!$this->getRequest()->getParam('storage_filter')) {
                $this->addColumn('stock_status',
                    array(
                    'header'=> 'Availability',
                    'width' => '60px',
                    'index' => 'stock_status',
                    'type'  => 'options',
                    'options' => array('1'=>'In stock','0'=>'Out of stock'),
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_stock',
                ));
            }
        }

        if ($this->getRequest()->getParam('storage_filter')) {
            $this->addColumn('stock_value',
                array(
                'header'=> 'Stock Value',
                'type'  => 'price',
                'index'=> 'stock_value',
                'width' => '60px',
                'align' => 'right',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ));

            $this->addColumn('sold_qty',
                array(
                'header'=> Mage::helper('catalog')->__('Sold Qty'),
                'width' => '20px',
                'index' => 'sold_qty',
                'type'  => 'number',
                'filter' => false,
                'sortable' => false,
            ));

            $this->addColumn('sold_value',
                array(
                'header'=> Mage::helper('catalog')->__('Sold Value'),
                'type'  => 'price',
                'index' => 'sold_value',
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'align' => 'right',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ));
        }

        /* Ticket 5492 - Added Status field into dxb grid*/

        if(!$this->getRequest()->getParam('storage_filter')){
         	$this->addColumn('status',
                array(
                    'header'=> Mage::helper('catalog')->__('Status'),
                    'width' => '70px',
                    'index' => 'status',
                    'type'  => 'options',
                    'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));
        }

        $this->addColumn('same_day_shipping',
            array(
            'header'=> Mage::helper('catalog')->__('SDS'),
            'width' => '20px',
            'index' => 'same_day_shipping',
            'type'  => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0'  => Mage::helper('catalog')->__('No')
            ),
            'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_sds'
        ));

        if (!$this->getRequest()->getParam('inbound_filter') && !$this->getRequest()->getParam('storage_filter')) {
            $this->addColumn('low_stock',
                array(
                'header'=> Mage::helper('catalog')->__('Low Stock'),
                'width' => '20px',
                'index' => 'low_stock',
                'type'  => 'number',
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_lowstock',
            ));
        }

        $this->addColumn('inbound',
            array(
                'header'=> Mage::helper('catalog')->__('Inbound'),
                'width' => '10px',
                'index' => 'inbound',
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_inbound',
                'filter'    => false,
                'sortable'  => false,
        ));

        if($this->getRequest()->getParam('storage_filter')){
            $this->addColumn('inbound_value',
                array(
                    'header'=> Mage::helper('catalog')->__('Inbound Value'),
                    'type'  => 'price',
                    'index' => 'inbound_value',
                    'filter' => false,
                    'sortable' => false,
                    'width' => '100px',
                    'align' => 'right',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
            ));
        }        

        if ($this->getRequest()->getParam('order_filter')) {
            $this->addColumn('order_report',
                array(
                'header'=> Mage::helper('catalog')->__('Order Qty'),
                'width' => '20px',
                'index' => 'low_stock',
                'type'  => 'text',
                'filter'    => false,
                'sortable'  => false,
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_orderqty'
            ));

            $this->addColumn('cost',
                array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'width' => '20px',
                'index' => 'cost',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ));
        }

        if(!$this->getRequest()->getParam('storage_filter')){
            $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

            $this->addColumn('set_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                    'width' => '130px',
                    'index' => 'attribute_set_id',
                    'type'  => 'options',
                    'options' => $sets,
            ));
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('websites',
                    array(
                        'header'=> Mage::helper('catalog')->__('Websites'),
                        'width' => '100px',
                        'sortable'  => false,
                        'index'     => 'websites',
                        'type'      => 'options',
                        'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
            }
        }

        if (!$this->getRequest()->getParam('inbound_filter') && !$this->getRequest()->getParam('storage_filter')) {
            $this->addColumn('update_last_at',
                array(
                    'header'=> Mage::helper('catalog')->__('Last Move'),
                    'width' => '80px',
                    'index' => 'update_last_at',
                    'type'  => 'date',
                    'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_track',
                    'filter_condition_callback' => array($this, '_filtertrack'),
            ));
        }

        if (!$this->getRequest()->getParam('inbound_filter') && !$this->getRequest()->getParam('storage_filter')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('catalog')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('catalog')->__('Edit'),
                            'url'     => array(
                                'base'=>'*/catalog_product/edit',
                                'params'=>array('store'=>$this->getRequest()->getParam('store'),'sds'=>1)
                            ),
                            'field'   => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
            ));
        }

        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/sds', Mage::helper('catalog')->__('Notify SDS Low Stock RSS'));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true,'sds'=>1))
            ));
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    protected function _filtertrack($collection, $column)
    {
        $store = $this->_getStore();
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $myDateTime = DateTime::createFromFormat('d/m/Y', $value['orig_from']);
        $from = $myDateTime->format('Y-m-d H:i:s');
        $myDateTime2 = DateTime::createFromFormat('d/m/Y', $value['orig_to']);
        $to = $myDateTime2->format('Y-m-d H:i:s');
        $ids = array();
        foreach ($collection as $field) {
            if ($field->getUpdateLastAt() >= $from && $field->getUpdateLastAt() <= $to) {
                $ids[] = $field->getId();
            }
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('part_nr')
            ->addAttributeToSelect('low_stock')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('same_day_shipping')
            ->addAttributeToSelect('dxbsp')
            ->addAttributeToSelect('sds_red')
            ->addAttributeToSelect('manufacturer');
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left' )
                ->joinTable( 'cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock") )->addAttributeToSelect('stock_status');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));
        $collection->getSelect()->order('update_last_at', 'DESC');
        
        $this->setCollection($collection);
        return $this;
    }

    protected function getDataCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('part_nr')
            ->addAttributeToSelect('low_stock')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('same_day_shipping')
            ->addAttributeToSelect('dxbsp')
            ->addAttributeToSelect('sds_red')
            ->addAttributeToSelect('manufacturer');


        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left' )
                ->joinTable( 'cataloginventory/stock_item', 'product_id=entity_id', array("stock_status" => "is_in_stock") )->addAttributeToSelect('stock_status');
        }
        if ($store->getId()) {
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        
        if($this->getRequest()->getParam('storage_filter')){
            
            $startDate = $_GET['startDate'];
            $endDate = $_GET['endDate'];
            $dateRange = ' AND  create_date BETWEEN "'.$startDate.' 00:00:00.000000" AND "'. $endDate .' 23:59:59.000000"';
            
            $collection->getSelect()->columns([
                'stock_value' => new Zend_Db_Expr(' (SELECT SUM(qty*cost) FROM gearup_sds_history as gsh WHERE 
                gsh.sds_status=1 AND gsh.product_id = e.entity_id '.$dateRange.' ) '),

                'sold_qty' => new Zend_Db_Expr('(SELECT sum(CONVERT(SUBSTRING_INDEX(in_out,"-",-1),UNSIGNED INTEGER)) FROM gearup_sds_history as gsh WHERE 
                gsh.sds_status=1 AND gsh.product_id = e.entity_id  '.$dateRange.' AND (actions like "%Order%" OR actions like "%order%") AND in_out like "%-%" )'),

                'sold_value' => new Zend_Db_Expr('(SELECT SUM(sold_qty*cost) FROM gearup_sds_history as gsh WHERE 
                gsh.sds_status=1 AND gsh.product_id = e.entity_id  '.$dateRange.' )'),

                'inbound_value' => new Zend_Db_Expr('(SELECT SUM(inbound*cost) FROM gearup_sds_history as gsh WHERE 
                gsh.sds_status=1 AND gsh.product_id = e.entity_id '.$dateRange.' )')
            ]);
        }
        
        $collection->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));

        if ($this->getRequest()->getParam('dxbsp_filter')) {
            $collection->addFieldToFilter('dxbsp', 1);
        }
        if ($this->getRequest()->getParam('sdsred_filter')) {
            $collection->addFieldToFilter('sds_red', 1);
        }

        return $collection;
    }

    protected function _getAttributeOptions($attribute_code, $mode)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
        $options = array();
        if ($mode) {
            $productsDxbsp = Mage::getModel('catalog/product')->getCollection();
            $productsDxbsp->addAttributeToSelect('manufacturer');
            $productsDxbsp->addFieldToFilter('dxbs', array('eq' => 1));
            $productsDxbsp->addFieldToFilter('dxbsp', array('eq' => 1));
            $productsDxbsp->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));
            $optionsDxbsp = array();
            foreach ($productsDxbsp as $productDxbsp) {
                $optionsDxbsp[] = $productDxbsp->getManufacturer();
            }
            foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
                if (in_array($option['value'], $optionsDxbsp)) {
                    $options[$option['value']] = $option['label'];
                }
            }
            return $options;
        } else {
            foreach( $attribute->getSource()->getAllOptions(true, true) as $option ) {
                $options[$option['value']] = $option['label'];
            }
            return $options;
        }
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
            switch ($columnIndex) {
                case 'update_last_at':
                    $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
                    break;

                default:
                    parent::_setCollectionOrder($column);
                    break;
            }
        }
        return $this;
    }
}
