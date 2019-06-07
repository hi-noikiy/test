<?php

class Hatimeria_OrderManager_Block_Adminhtml_Period_View extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = Mage::helper('hordermanager')->__('Add Order');
        $this->setTemplate('hordermanager/view.phtml');
    }

    protected function _prepareLayout()
    {

    }

    public function getPeriod()
    {
        $periodId = $this->getRequest()->getParam('period_id');
        $period = Mage::getModel('hordermanager/period')->load($periodId);

        if ($period->isObjectNew()) {
            Mage::throwException('Not found!');
        }

        return $period;
    }

    public function getOrders()
    {
        $ordersCollection = Mage::getResourceModel('hordermanager/sales_order_collection');
        $ordersCollection->setPeriodFilter(Mage::registry('current_period'));
        $ordersCollection->filterVisible();
        $ordersCollection->filterStatus();
        $ordersCollection->setOrder('order_id', 'ASC');

        return $ordersCollection;
    }

    public function loadPeriodOrderItem($item, $order)
    {
        $itemsCollection = Mage::getModel('hordermanager/order_item')
            ->getCollection()
            ->addFieldToFilter('item_id', $item->getId())
            ->addFieldToFilter('order_id', $order->getId());

        foreach ($itemsCollection as $item) {
            return $item;
        }
    }

    public function loadSupplierAndAdminNotes($item, $order)
    {
        $ordersCollection = Mage::getModel('hordermanager/order_item')
            ->getCollection()
            ->addFieldToFilter('item_id', $item->getId())
            ->addFieldToFilter('order_id', $order->getId());

        foreach ($ordersCollection as $comment) {
            return $comment;
        }
    }

    public function getStatusClass($order)
    {
        $items = $order->getAllItems();

        $numberOfItems = count($items);
        $checkedItems = 0;
        $orderedItems = 0;
        $statusClass = 'red';

        foreach ($items as $item) {
            $itemId = $item->getId();
            $orderId = $item->getOrderId();

            $itemsCollection = Mage::getModel('hordermanager/order_item')->getCollection()
                ->addFieldToFilter('item_id', $itemId)
                ->addFieldToFilter('order_id', $orderId);

            $currentItem = $itemsCollection->getFirstItem();

            if ($currentItem->getInStock()){
                $checkedItems += 1;

                if ($numberOfItems == $checkedItems) {
                    $statusClass = 'green';

                    return $statusClass;
                }
            } elseif ($currentItem->getOrdered()) {
                $orderedItems += 1;

                if ($numberOfItems == $orderedItems) {
                    $statusClass = 'yellow';

                    return $statusClass;
                }
            } else {
                $statusClass = 'red';
            }
        }
        return $statusClass;
    }

    public function getFormattedDate($date)
    {
        $date = new DateTime($date);
        $date = $date->format('d-m-y H:i:s');

        return $date;
    }

    public function loadLastPeriod()
    {
        $itemsCollection = Mage::getModel('hordermanager/period')->getCollection();
        $itemsCollection->setOrder('period_id', 'DESC');
        return $itemsCollection->getFirstItem();
    }

    public function loadFirstPeriod()
    {
        $itemsCollection = Mage::getModel('hordermanager/period')->getCollection();
        $itemsCollection->setOrder('period_id', 'ASC');
        return $itemsCollection->getFirstItem();
    }

    public function loadAllPeriod()
    {
        $itemsCollection = Mage::getModel('hordermanager/period')->getCollection();
        $itemsCollection->setOrder('period_id', 'DESC');
        return $itemsCollection;
    }

}
