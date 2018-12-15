<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Resource_Report_Rma_Brand_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_reportType;
    protected $_selectedColumns = array();

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('rma/item');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where($this->_periodFormat.' >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where($this->_periodFormat.' <= ?', $this->_to);
        }

        return $this;
    }

    public function _applyStoresFilter()
    {
    }

    public function setFilterData($filterData)
    {
        // if (isset($filterData['report_type'])) {
        //     $this->_reportType = $filterData['report_type'];
        // }
        return $this;
    }

    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM main_table.created_at)';
        } else {
            $this->_periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m-%d\')';
        }

        $this->_selectedColumns = array(
            'created_at' => $this->_periodFormat,
            'qty_returns' => 'count(qty_requested)',
            'qty_items' => 'sum(qty_requested)',
        );

        // if ($this->isTotals()) {
        // }

        // if ($this->isSubTotals()) {
        // }
        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(array('main_table' => $this->getResource()->getMainTable()), $this->_getSelectedColumns());

        // Wrong select for configurables fix - when product is configurable, make join on SKU
        $select->joinLeft(array('order_item' => $this->getTable('sales/order_item')), 'main_table.order_item_id = order_item.item_id',
            array('product_sku' => 'IF(INSTR(order_item.sku, "-") > 0 AND product.type_id != "simple", LEFT(order_item.sku, INSTR(order_item.sku, "-") - 1) , order_item.sku)'));
        $select->joinLeft(array('product' => $this->getTable('catalog/product')), 'product.sku = IF(INSTR(order_item.sku, "-") > 0 AND product.type_id != "simple", LEFT(order_item.sku, INSTR(order_item.sku, "-") - 1) , order_item.sku)',
            array('product_real_id' => 'product.entity_id'));

        // Old selects - just for debug purpose
        //$select->joinLeft(array('customer' => $this->getTable('customer/customer')), 'main_table.customer_id = customer.customer_id', array('customer_name' => 'customer.name'));
        //$select->joinLeft(array('product' => $this->getTable('catalog/product')), 'main_table.product_id = product.entity_id', array('product_sku' => 'product.sku'));
        //$select->joinLeft(array('customer' => $this->getTable('customer/customer')), 'main_table.customer_id = customer.customer_id', array('customer_name' => 'customer.name'));
        //$select->joinLeft(array('product_varchar' => $this->getTable('catalog/product')), 'main_table.product_id = product.entity_id', array('product_sku' => 'product.sku'));
        //$select->joinLeft(array('customer' => $this->getTable('customer/customer')), 'main_table.customer_id = customer.customer_id', array('customer_name' => 'customer.name'));

        $brandAttributeCode = Mage::getSingleton('rma/config')->getGeneralBrandAttribute();
        if (!$brandAttributeCode) {
            die('Code of Brand Attribute is empty. Please, set it via RMA configuration.');
        }
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $brandAttributeCode);
        if (!$attribute->getId()) {
            die("Can't find attribute '<b>$brandAttributeCode</b>'. Please, check RMA configuration.");
        }

        // alias then field name
        $productAttributes = array('product_brand' => $brandAttributeCode);
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode.'_table';
            $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);

            //Add eav attribute value
            $this->getSelect()->joinLeft(
                    array($tableAlias => $attribute->getBackendTable()),
                    "product.entity_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}",
                    array($alias => 'value')
            );
            $tableOptionsAlias = $attributeCode.'_option_table';
            //Add eav attribute value
            $this->getSelect()->joinLeft(
                    array($tableOptionsAlias => Mage::getConfig()->getTablePrefix().'eav_attribute_option_value'),
                    "$tableAlias.value = $tableOptionsAlias.option_id AND $tableOptionsAlias.store_id = 0",
                    array($alias.'_option' => 'value')
            );
        }
        $select->where('qty_requested > 0');

        if (!$this->isTotals() && !$this->isSubTotals()) {
            //поля по которым будут сделаны группировки при выводе отчета
            $select->group(array(
                $this->_periodFormat,
                'product_brand',
            ));
        }
        if ($this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat,
            ));
        }

//         echo $this->getSelect();
        return $this;
    }

    /************************/
}
