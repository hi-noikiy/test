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



/**
 * @method Mirasvit_Rma_Model_Rma getFirstItem()
 * @method Mirasvit_Rma_Model_Rma getLastItem()
 * @method Mirasvit_Rma_Model_Resource_Rma_Collection|Mirasvit_Rma_Model_Rma[] addFieldToFilter
 * @method Mirasvit_Rma_Model_Resource_Rma_Collection|Mirasvit_Rma_Model_Rma[] setOrder
 */
class Mirasvit_Rma_Model_Resource_Rma_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rma/rma');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('rma')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Rma_Model_Rma $item */
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $arr;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('rma')->__('-- Please Select --');
        }
        /** @var Mirasvit_Rma_Model_Rma $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @param int $storeId
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function addStoreIdFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('rma/rma_store')}`
                AS `rma_store_table`
                WHERE main_table.rma_id = rma_store_table.rs_rma_id
                AND rma_store_table.rs_store_id in (?))", array(0, $storeId));

        return $this;
    }

    /**
     * @param int $exchangeOrderId
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function addExchangeOrderFilter($exchangeOrderId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('rma/rma_order')}`
                AS `rma_order_table`
                WHERE main_table.rma_id = rma_order_table.re_rma_id
                AND rma_order_table.re_exchange_order_id in (?))", array(-1, $exchangeOrderId));

        return $this;
    }

    /**
     * @param int $creditMemoId
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function addCreditMemoFilter($creditMemoId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('rma/rma_creditmemo')}`
                AS `rma_creditmemo_table`
                WHERE main_table.rma_id = rma_creditmemo_table.rc_rma_id
                AND rma_creditmemo_table.rc_credit_memo_id in (?))", array(-1, $creditMemoId));

        return $this;
    }

    /**
     * @param int $orderId
     *
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function addOrderIdFilter($orderId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('rma/item')}`
                AS `rma_item`
                WHERE main_table.rma_id = rma_item.rma_id
                AND rma_item.order_id in (?))", array(-1, $orderId));

        return $this;
    }

    /**
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function joinRmaItems()
    {
        /* @noinspection PhpUnusedLocalVariableInspection */
        $select = $this->getSelect();
        $select->joinInner(array(
            'items' => $this->getTable('rma/item'), ),
            'main_table.rma_id = items.rma_id',
            array('order_id' => 'items.order_id')
        );
        $select->group('main_table.rma_id');

        return $this;
    }

    /**
     * @return Mirasvit_Rma_Model_Resource_Rma_Collection
     */
    public function joinRmaOrders()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            array('items' => $this->getTable('rma/item')),
            'main_table.rma_id = items.rma_id',
            array('order_id' => 'items.order_id')
        );
        $select->joinLeft(
            array('orders' => $this->getTable('sales/order')),
            'items.order_id = orders.entity_id',
            array()
        );

        $select->joinLeft(
            array('offline_items' => $this->getTable('rma/offline_item')),
            'main_table.rma_id = offline_items.rma_id',
            array('offline_order_id' => 'offline_items.offline_order_id')
        );
        $select->joinLeft(
            array('offline_orders' => $this->getTable('rma/offline_order')),
            'offline_items.offline_order_id = offline_orders.offline_order_id',
            array()
        );
        $select->group('main_table.rma_id');
            $select->columns('CONCAT_WS(\', \',
            GROUP_CONCAT(DISTINCT (orders.increment_id) SEPARATOR \', \'),
            GROUP_CONCAT(DISTINCT (offline_orders.receipt_number) SEPARATOR \', \'))
            AS order_increment_id');
        return $this;
    }

    /**
     * @return void
     */
    protected function initFields()
    {
        $select = $this->getSelect();
        $select->joinLeft(array('status' => $this->getTable('rma/status')),
            'main_table.status_id = status.status_id', array('status_name' => 'status.name'));
        $select->columns(array('name' => new Zend_Db_Expr("CONCAT(firstname, ' ', lastname)")));
    }

    /**
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelectCountSql();
            $this->getConnection()->fetchAll($sql, $this->_bindParams);
            $this->_totalRecords = $this->getConnection()->query('SELECT FOUND_ROWS()')->fetchColumn();
        }
        return intval($this->_totalRecords);
    }

     /************************/
}
