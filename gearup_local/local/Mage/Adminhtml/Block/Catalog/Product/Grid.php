<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                //->addAttributeToSelect('discount as (special_price*100)/price) ')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id')
                ->addAttributeToSelect('same_day_shipping')
                ->addAttributeToSelect('part_nr')
                ->addAttributeToSelect('is_custom')
                ->addAttributeToSelect('dxbsp');

        $collection->joinAttribute(
            'manufacturer',
            'catalog_product/manufacturer',
            'entity_id',
            null,
            'left',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );        

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                    'name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore
            );
            $collection->joinAttribute(
                    'custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                    'status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                    'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                    'price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId()
            );
            $collection->joinAttribute(
                    'discount', 'catalog_product/discount', 'entity_id', null, 'left', $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }


        if ($speciasl_price = Mage::app()->getRequest()->getParam('special_price')) {
            $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $collection->addAttributeToSelect('*')->addAttributeToFilter('special_price', array('gt' => 0, 'left')
            );
        }

        $collection->joinAttribute(
                'special_price', 'catalog_product/special_price', 'entity_id', null, 'left', $store->getId()
        );

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _addColumnFilterToCollection($column) {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites', 'catalog/product_website', 'website_id', 'product_id=entity_id', null, 'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'entity_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
            'width' => '400px',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name', array(
                'header' => Mage::helper('catalog')->__('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
        }

        $this->addColumn('type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'width' => '60px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '130px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $this->addColumn('part_nr', array(
            'header' => Mage::helper('catalog')->__('Part number'),
            'width' => '150px',
            'index' => 'part_nr',
            'renderer' => 'Mage_Adminhtml_Block_Catalog_Product_Renderer_Partnumber',
        ));

        $store = $this->_getStore();
        $this->addColumn('price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'type' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
        ));
        $this->addColumn('special_price', array(
            'header' => Mage::helper('catalog')->__('Special Price'),
            'type' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'special_price',
            'order_callback' => array($this, 'sortBySpecialPrice')
        ));

        $this->addColumn('special_price2', array(
            'header' => Mage::helper('catalog')->__('Discount %'),
            'index' => 'special_price',
            'sortable' => false,
            'filter' => false,
            'width' => '50px',
            'renderer' => 'Mage_Adminhtml_Block_Catalog_Product_Renderer_Discount'
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty', array(
                'header' => Mage::helper('catalog')->__('Qty'),
                'width' => '100px',
                'type' => 'number',
                'index' => 'qty',
            ));
        }



        $this->addColumn('visibility', array(
            'header' => Mage::helper('catalog')->__('Visibility'),
            'width' => '70px',
            'index' => 'visibility',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '70px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites', array(
                'header' => Mage::helper('catalog')->__('Websites'),
                'width' => '100px',
                'sortable' => false,
                'index' => 'websites',
                'type' => 'options',
                'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }

        $this->addColumn('same_day_shipping', array(
            'header' => Mage::helper('catalog')->__('SDS'),
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No'),
            'index' => 'same_day_shipping',
            'width' => '50px',
            'sortable' => false,
        ));

        $this->addColumn('is_custom', array(
            'header' => Mage::helper('catalog')->__('Is Custom'),
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No'),
            'index' => 'is_custom',
            'width' => '50px',
            'sortable' => false,
        ));

        $this->addColumn('discontinued_product', array(
            'header' => Mage::helper('catalog')->__('Discontinued'),
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No'),
            'index' => 'discontinued_product',
            'width' => '50px',
            'sortable' => false,
        ));

        $this->addColumn('cnet_desc', array(
            'header' => Mage::helper('catalog')->__('Cnet Description'),
            'type' => 'options',
            'options' => array('1' => 'Yes', '0' => 'No'),
            'index' => 'cnet_desc',
            'width' => '50px',
            'sortable' => false,
        ));

        $attribute  = Mage::getResourceModel('catalog/product')->getAttribute('manufacturer');
        $preOptions = $attribute->getSource()->getAllOptions(false);

        $options = array();
        foreach($preOptions as $option) {
            if($option['value']) {
                $options[$option['value']] = $option['label'];
            }
        }

        $this->addColumn('manufacturer', array(
             'header'  => $this->__($attribute->getFrontendLabel()),
             'width'   => '100px',
             'type'    => 'options',
             'index'   => $attribute->getAttributeCode(),
             'options' => $options,
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('catalog')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('catalog')->__('Edit'),
                    'url' => array(
                        'base' => '*/*/edit',
                        'params' => array('store' => $this->getRequest()->getParam('store'))
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        }

        return parent::_prepareColumns();
    }

    protected function _setCollectionOrder($column) {
        if ($column->getOrderCallback()) {
            call_user_func($column->getOrderCallback(), $this->getCollection(), $column);

            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

    public function sortBySpecialPrice($collection, $column) {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    public function getMainButtonsHtml() {
        $html = parent::getMainButtonsHtml();
        $specialPriceFilter = $this->getRequest()->getParam('special_price');
        $add_specialprice_filter_button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
            'label' => (!$specialPriceFilter) ?
                    $this->__('Apply Special Price Filter') : $this->__('Remove Special Price Filter'),
            'onclick' => "window.location.href='" . $this->getUrl('*/*', array('special_price' => (!$specialPriceFilter) ? '1' : '0')) . "'",
            'class' => 'save'
        ));
        $html = $add_specialprice_filter_button->toHtml() . $html;
        return $html;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('catalog')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('catalog')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
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

        if (Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes')) {
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('catalog')->__('Update Attributes'),
                'url' => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current' => true))
            ));
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array(
                    'store' => $this->getRequest()->getParam('store'),
                    'id' => $row->getId())
        );
    }

}
