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
 * @package   mirasvit/extension_advr
 * @version   1.2.11
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Advr_Model_Report_Customers extends Mirasvit_Advr_Model_Report_Abstract
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_init('sales/order');

        $this->relations = array(
            array(
                'customer/entity',
                'customer/customer_group',
                'customer_entity_table.group_id = customer_customer_group_table.customer_group_id',
            ),
            array(
                'customer/entity',
                'customer/address_entity',
                'customer_entity_table.entity_id = customer_address_entity_table.parent_id',
            ),
            array(
                'customer/address_entity',
                'sales/order_address',
                'sales_order_address_table.customer_address_id = customer_address_entity_table.entity_id',
            ),
            array(
                'sales/order',
                'customer/entity',
                'sales_order_table.customer_id = customer_entity_table.entity_id',
            ),
            array(
                'sales/order',
                'sales/order_item',
                'sales_order_table.entity_id = sales_order_item_table.order_id',
            ),
            array(
                'catalog/product',
                'cataloginventory/stock_item',
                'catalog_product_table.entity_id = cataloginventory_stock_item_table.product_id',
            ),
            array(
                'customer/entity',
                'log/customer',
                'customer_entity_table.entity_id = log_customer_table.customer_id',
            ),
        );

        $this->addColumn(
            'customer_id',
            array(
                'label' => false,
                'expression' => 'customer_entity_table.entity_id',
                'table' => 'customer/entity',
            )
        )->addColumn(
            'email',
            array(
                'label' => false,
                'expression' => 'customer_entity_table.email',
                'table' => 'customer/entity',
            )
        )->addColumn(
            'customer_group_id',
            array(
                'label' => false,
                'expression' => 'customer_entity_table.group_id',
                'table' => 'customer/entity',
            )
        )->addColumn(
            'customer_created_at',
            array(
                'label' => false,
                'expression' => 'customer_entity_table.created_at',
                'table' => 'customer/entity',
            )
        )->addColumn(
            'last_order_at',
            array(
                'label' => false,
                'expression' => 'MAX(sales_order_table.created_at)',
                'table' => 'sales/order',
            )
        )->addColumn(
            'last_login_at',
            array(
                'label' => false,
                'expression' => 'MAX(log_customer_table.login_at)',
                'table' => 'log/customer',
            )
        )->addColumn(
            'quantity',
            array(
                'label' => false,
                'expression' => 'COUNT(DISTINCT(sales_order_table.entity_id))',
                'table' => 'sales/order',
            )
        )->addColumn(
            'sum_total_qty_ordered',
            array(
                'label' => false,
                'expression' => 'SUM(sales_order_table.total_qty_ordered)',
                'table' => 'sales/order',
            )
        )->addColumn(
            'sum_grand_total',
            array(
                'label' => false,
                'expression' => 'SUM(sales_order_table.base_grand_total)',
                'table' => 'sales/order',
                'type' => 'currency',
            )
        )->addColumn(
            'avg_grand_total',
            array(
                'label' => false,
                'expression' => 'AVG(sales_order_table.base_grand_total)',
                'table' => 'sales/order',
                'type' => 'currency',
            )
        );

        $customerAttributes = Mage::getSingleton('advr/system_config_source_customerAttribute')->toOptionHash();
        foreach ($customerAttributes as $attrCode => $attrLabel) {
            if (isset($this->columns['customer_'.$attrCode])) {
                continue;
            }

            $this->addColumn(
                'customer_'.$attrCode,
                array(
                    'label' => false,
                    'expression' => 'customer_'.$attrCode.'_table.value',
                    'table_method' => 'advr/report_table_customerAttribute',
                    'table_args' => array(
                        'attribute' => $attrCode,
                    ),
                )
            );
        }

        $addressAttributes = Mage::getSingleton('advr/system_config_source_customerAddressAttribute')->toOptionHash();
        foreach ($addressAttributes as $attrCode => $attrLabel) {
            if (isset($this->columns['customer_address_'.$attrCode])) {
                continue;
            }

            $this->addColumn(
                'customer_address_'.$attrCode,
                array(
                    'label' => false,
                    'expression' => 'customer_address_'.$attrCode.'_table.value',
                    'table_method' => 'advr/report_table_customerAddressAttribute',
                    'table_args' => array(
                        'attribute' => $attrCode,
                    ),
                )
            );
        }

        $this->addColumn(
            'products',
            array(
                'label' => false,
                'expression' => 'GROUP_CONCAT(sales_order_item_subquery_table.products SEPARATOR "@")',
                'table_method' => 'advr/report_table_orderItemSubquery',
            )
        );

        return $this;
    }

    public function setFilterData($data, $filterByStatus = true)
    {
        parent::setFilterData($data);

        $this->filterData = $data;

        $conditions = array();

        if (count($this->filterData->getStoreIds())) {
            $conditions[] = 'customer_entity_table.store_id IN('.implode(',', $this->filterData->getStoreIds()).')';
        }

        foreach ($conditions as $condition) {
            $this->getSelect()->where($condition);
        }

        return $this;
    }
}
