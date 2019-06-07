<?php

class Gearup_Sds_Block_Adminhtml_Period_View extends Hatimeria_OrderManager_Block_Adminhtml_Period_View
{
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = Mage::helper('hordermanager')->__('Add Order');
        $this->setTemplate('gearup/hordermanager/view.phtml');
    }

    public function getSdsclass($sds)
    {
        if ($sds) {
            return 'green';
        } else {
            return '';
        }
    }

    public function getShipped($order)
    {
        if (in_array($order->getStatus(), $this->isShipped())) {
            return true;
        } else {
            return false;
        }
    }

    public function isShipped() {
        $shipped = array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Gearup_Shippingffdx_Model_Tracktype::STATE_PROCESS_SHIPPED,
            Gearup_Shippingffdx_Model_Tracktype::STATE_NEW_SHIPPED,
            Gearup_Shippingffdx_Model_Tracktype::STATE_COMPLETE_DELIVERED,
            Gearup_Shippingffdx_Model_Tracktype::STATE_PROCESS_DELIVERED
        );
        return $shipped;
    }

    public function getStatusClass($order, $sdsdMark=null)
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
            } elseif ($currentItem->getOrdered() || $sdsdMark) {
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

    public function getStatusClassbyItem($item, $sdsdMark=null)
    {
        $statusClass = 'red';

        $itemId = $item->getItemId();
        $orderId = $item->getOrderId();

        $itemsCollection = Mage::getModel('hordermanager/order_item')->getCollection()
            ->addFieldToFilter('item_id', $itemId)
            ->addFieldToFilter('order_id', $orderId);

        $currentItem = $itemsCollection->getFirstItem();

        if ($currentItem->getInStock()){
            $statusClass = 'green';
            return $statusClass;
        } elseif ($currentItem->getOrdered() || $sdsdMark) {
            $statusClass = 'yellow';
            return $statusClass;
        } else {
            $statusClass = 'red';
        }

        return $statusClass;
    }

}
