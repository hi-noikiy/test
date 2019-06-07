<?php
class Gearup_Sds_Block_Adminhtml_Export_Inbound extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection->getSelect()->joinLeft(array("last" => 'gearup_sds_tracking'), "e.entity_id = last.product_id", array("update_last_at" => "last.update_last_at", "order_id" => "last.order_id", "inbound" => "last.inbound"));
        $collection->getSelect()->where("last.inbound != ''");
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

    /* Ticket 5492 
     -- Add Price and cost field into download csv */
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

        $this->addColumn('inbound',
            array(
                'header'=> Mage::helper('catalog')->__('Inbound'),
                'width' => '10px',
                'index' => 'inbound',
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
                'width' => '185px',
                'index' => 'price',
        ));

        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'type'  => 'price',
                'width' => '185px',
                'index' => 'cost',
        ));

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /*Ticket-5492 
      -- Add price and cost field into download csv file.
      */
    protected function getDataCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('part_nr')
            ->addAttributeToSelect('low_stock')
            ->addAttributeToSelect('same_day_shipping')
            ->addAttributeToSelect('cost');

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
                'cost',
                'catalog_product/cost',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('cost');
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->joinAttribute('cost', 'catalog_product/cost', 'entity_id', null, 'inner');
        }

        return $collection;
    }
}
