<?php
/**
 * Sales Order Collection
 */

class Hatimeria_OrderManager_Model_Resource_Sales_Order_Collection extends Mage_Sales_Model_Resource_Order_Collection
{
    /**
     * @see Mage_Core_Model_Resource_Db_Collection_Abstract
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _initSelect()
    {
        $collection = parent::_initSelect();
        $collection->getSelect()
            ->joinInner(array('pho' => $this->getTable('hordermanager/order')),
                'main_table.entity_id=pho.order_id',
                array(
                    'is_hidden' =>'is_hidden',
                    'order_link_id' => 'period_has_order_id'
                )
            );

        return $collection;
    }

    /**
     * Set period filter
     * @param Hatimeria_OrderManager_Model_Period $period
     * @internal param $periodId
     * @return $this
     */
    public function setPeriodFilter(Hatimeria_OrderManager_Model_Period $period)
    {
        $this->addFieldToFilter('period_id', $period->getId());

        return $this;
    }

    public function filterVisible()
    {
        $this->addFieldToFilter('is_hidden', 0);
    }

    public function filterStatus()
    {
        $this->addFieldToFilter('status', array('neq' => 'canceled'));
        $this->addFieldToFilter('status', array('neq' => 'closed'));
        $this->addFieldToFilter('status', array('neq' => 'holded'));
        $this->addFieldToFilter('status', array('neq' => 'pending'));
        $this->addFieldToFilter('status', array('neq' => 'pending_payment'));
        $this->addFieldToFilter('status', array('neq' => 'fraud'));
    }
} 