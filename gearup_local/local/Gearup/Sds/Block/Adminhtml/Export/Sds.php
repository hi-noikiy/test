<?php
class Gearup_Sds_Block_Adminhtml_Export_Sds extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setDefaultLimit(200);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('part_nr')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToSelect('low_stock')
            ->addAttributeToSelect('same_day_shipping');

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
            $collection->joinAttribute(
                'manufacturer',
                'catalog_product/manufacturer',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute(
                'special_price',
                'catalog_product/special_price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute(
                'cost',
                'catalog_product/cost',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('special_price');
            $collection->addAttributeToSelect('cost');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $collection->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));

        $collection->addFieldToFilter('dxbs', 1);
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
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
                'width' => '80px',
                'index' => 'sku',
        ));

        $this->addColumn('part_nr',
            array(
                'header'=> Mage::helper('catalog')->__('Part number'),
                'width' => '185px',
                'index' => 'part_nr',
        ));

        $this->addColumn('manufacturer',
            array(
                'header'=> Mage::helper('catalog')->__('Manufacturer'),
                'width' => '185px',
                'index' => 'manufacturer',
                'type'  => 'options',
                'options'   => $this->_getAttributeOptions('manufacturer', 1),
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_manufacturer',
        ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                /*'currency_code' => $store->getBaseCurrency()->getCode(),*/
                'index' => 'price',
        ));

        $store = $this->_getStore();
        $this->addColumn('special_price',
            array(
                'header'=> Mage::helper('catalog')->__('Special Price'),
                'type'  => 'special_price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'special_price',
        ));

        $store = $this->_getStore();
        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'type'  => 'cost',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'cost',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Stock Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
            ));

            $this->addColumn('stock_status',
                array(
                'header'=> 'Availability',
                'width' => '60px',
                'index' => 'stock_status',
                'type'  => 'options',
                'options' => array('1'=>'In stock','0'=>'Out of stock'),
            ));
        }

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('same_day_shipping',
            array(
            'header'=> Mage::helper('catalog')->__('SDS'),
            'width' => '20px',
            'index' => 'same_day_shipping',
            'type'  => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0'  => Mage::helper('catalog')->__('No')
            )
        ));

        $this->addColumn('low_stock',
            array(
            'header'=> Mage::helper('catalog')->__('Low Stock'),
            'width' => '20px',
            'index' => 'low_stock',
            'type'  => 'number',
        ));

        $this->addColumn('inbound',
            array(
                'header'=> Mage::helper('catalog')->__('Inbound'),
                'width' => '10px',
                'index' => 'inbound',
                'type'  => 'number',
                'renderer' => 'gearup_sds/adminhtml_sds_grid_column_renderer_inbound',
        ));

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

        $this->addColumn('update_last_at',
            array(
                'header'=> Mage::helper('catalog')->__('Last move'),
                'width' => '80px',
                'index' => 'update_last_at',
                'type'  => 'date',
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

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
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
