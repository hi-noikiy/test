<?php

class Hatimeria_OrderManager_Model_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('hordermanager/order');
    }

    /**
     * @return Object
     */
    public function _prepareCollection()
    {

        $collection = Mage::getResourceModel('hordermanager/order_collection');
        $collection->addAttributeToSelect(array('period_id', 'order_id', 'is_hidden'));
        $collection->addAttributeToSelect('*');

        $collection->addFieldToFilter('order_id', 10);
        $collection->load();

        return $collection;
    }

    /**
     * Save comments of order
     * @param $data
     */
    public function saveComments($data)
    {
        $defaults = array(
            'supplier_notes'    => '',
            'admin_notes'       => ''
        );

        $mergedData = array_merge($defaults, $data);
        $this
            ->addData($mergedData)
            ->save();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        if ($this->hasData('items')) {
            $items = $this->getItems();
            Mage::getModel('hordermanager/order_item')->saveIfOrderedAndInStock($items);
        }
    }

    public function loadPeriodByOrderId($orderId)
    {
        $collection = $this->getCollection();
        $select = $collection->getSelect();
        $period = Mage::getModel('hordermanager/period')->getResource();
        $select
            ->joinLeft(array('hper' => $period->getTable('hordermanager/period')),
                'main_table.period_id=hper.period_id',
                array(
                    'estimated_shipping' => 'estimated_shipping'
                )
            )->where("order_id = '{$orderId}'");

        return $collection->getFirstItem();
    }
} 