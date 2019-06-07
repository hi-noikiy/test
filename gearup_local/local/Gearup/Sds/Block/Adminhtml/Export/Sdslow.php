<?php
class Gearup_Sds_Block_Adminhtml_Export_Sdslow extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = $this->getDataCollection();
        $collection->addFieldToFilter('dxbs', 1);
        $collection->addFieldToFilter('low_stock', array('neq'=>''));
        $lowids = array();
        foreach ($collection as $obj) {
            if ($obj->getQty() < $obj->getLowStock()) {
                $lowids[] = $obj->getEntityId();
            }
        }
        $productCollection = $this->getDataCollection();
        $productCollection->addFieldToFilter('entity_id', array('in'=>$lowids));
        $this->setCollection($productCollection);

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
//        $this->addColumn('entity_id',
//            array(
//                'header'=> Mage::helper('catalog')->__('Product ID'),
//                'width' => '50px',
//                'type'  => 'number',
//                'index' => 'entity_id',
//        ));
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

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Qty'),
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

        $this->addColumn('low_stock',
            array(
            'header'=> Mage::helper('catalog')->__('Low Stock'),
            'width' => '20px',
            'index' => 'low_stock',
            'type'  => 'number',
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

//        $this->addColumn('status',
//            array(
//                'header'=> Mage::helper('catalog')->__('Status'),
//                'width' => '70px',
//                'index' => 'status',
//                'type'  => 'options',
//                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
//        ));

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

    protected function getDataCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('part_nr')
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
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        return $collection;
    }
}
