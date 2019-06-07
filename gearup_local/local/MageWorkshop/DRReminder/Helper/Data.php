<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRReminder_Helper_Data extends Mage_Core_Helper_Abstract
{

    const DRREMINDER_XML_PATH_MODULE_ENABLE = 'drreminder/settings/remind_enable';
    const DRREMINDER_MODULE_NAME = 'MageWorkshop_DRReminder';
    const DRREMINDER_PACKAGE_FILE = 'DRReminder';
    const DRREMINDER_UNINSTALL_PATH = 'drreminder/uninstall';

    /**
     * @param $order Mage_Sales_Model_Order
     */
    public function createReviewReminder($order)
    {
        $allowedStatuses = explode(',', Mage::getStoreConfig('drreminder/settings/remind_choice_status'));
        if (in_array($order->getStatus(), $allowedStatuses)) {
            $remindersCollection = Mage::getModel('drreminder/reminder')->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $order->getId()));
            if (!$remindersCollection->getSize()) {
                $productCollection = Mage::helper('drreminder')->getProductsByOrders($order->getId());
                if ($productCollection->count()) {
                    $delay = Mage::getStoreConfig('drreminder/settings/remind_delay_period');
                    /** @var MageWorkshop_DRReminder_Model_Reminder $reminder */
                    $reminder = Mage::getModel('drreminder/reminder');
                    $reminder
                        ->setCustomerId($order->getCustomerId())
                        ->setCustomerName($order->getCustomerFirstname().' '.$order->getCustomerLastname())
                        ->setEmail($order->getCustomerEmail())
                        ->setOrderId($order->getId())
                        ->setIncrementId($order->getIncrementId())
                        ->setCreatingDate(Mage::getModel('core/date')->gmtDate())
                        ->setExpirationDate(Mage::getModel('core/date')->gmtDate(null, (Mage::getModel('core/date')->timestamp(time())) + 60*60*24*$delay))
                        ->setStoreId($order->getStoreId());
                    Mage::dispatchEvent('drreminder_reminder_create', array(
                        'reminder' => $reminder,
                        'order'    => $order
                    ));
                    $reminder->save();
                    if (!$delay && $this->isSendingAllowedNow() && Mage::getStoreConfig('drreminder/settings/remind_send_email')) {
                        $reminder->sendReminderEmail();
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $customerId
     * @param $orderId
     * @return Mage_Sales_Model_Resource_Order_Item_Collection
     */
    public function getReminderItems($customerId, $orderId)
    {
        $customerOrders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id',array('eq' => $customerId));
        $orderIds = array();
        foreach ($customerOrders as $customerOrder) {
            $orderIds[] = $customerOrder->getId();
            if ($customerOrder->getId() == $orderId){
                $productIds = array();
                $currentItems = $customerOrder->getAllItems();
                foreach ($currentItems as $current) {
                    $productIds[] = $current->getProductId();
                }
            }
        }
        $customerItems = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id',array('in' => $orderIds))
            ->addFieldToFilter('product_id',array('in' => $productIds));
        return $customerItems;
    }

    /**
     * @param $items
     * @return array
     */
    public function getItemsWithSentReminders($items)
    {
        $sent = array();
        foreach ($items as $item) {
            if ($item->getReminder()) {
                $sent[] =  $item->getProductId();
            }
        }
        $unique = array_unique($sent);
        return $unique;
    }

    public function getCustomerIdentifier()
    {
        // http://example.com/drreminder/reminder/products/order/fdsfgkjest89j9034airk03a/ order
        $identifier = (string) Mage::app()->getRequest()->getParam('order');
        if (isset($this->_customerIdentifier) && $this->_customerIdentifier && $this->_customerIdentifier->getHash() == $identifier) {
            return $this->_customerIdentifier;
        }

        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->getSelect()
            ->having('MD5(CONCAT(entity_id, created_at)) = ?', $identifier);

        /** @var Mage_Sales_Model_Order $order */
        $order = $orderCollection->getFirstItem();

        if ($order->getId()) {
            /** @var MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier */
            $customerIdentifier = Mage::getModel('drreminder/customerIdentifier');
            if ($order->getCustomerId()) {
                $data = array(
                    'type'  => $customerIdentifier::IDENTIFIER_TYPE_ID,
                    'value' => $order->getCustomerId()
                );
            } else {
                $data = array(
                    'type'  => $customerIdentifier::IDENTIFIER_TYPE_EMAIL,
                    'value' => $order->getCustomerEmail()
                );
            }
            $data['order_id'] = $order->getId();
            $data['hash'] = $identifier;
            $customerIdentifier->setData($data);

            $this->_customerIdentifier = $customerIdentifier;
            Mage::getSingleton('core/cookie')->set('customerIdentifier', json_encode($customerIdentifier->getData()), 60*60*24*10);
            Mage::getSingleton('core/cookie')->set('store', $order->getStore()->getCode(), 60*60*24*10);
            
            return $customerIdentifier;
        } else {
            return false;
        }
    }

    /**
     * @param $orderIds
     * @param bool $showOutOfStockProduct
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsByOrders($orderIds, $withoutRemindersOnly = true, $showOutOfStockProduct = true)
    {
        $productIds = array();

        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('entity_id', array('in' => $orderIds))
            ->addFieldToFilter(
                'state',
                array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
            );
        $allowedStatuses = explode(',', Mage::getStoreConfig('drreminder/settings/remind_choice_status'));
        $orderCollection->addFieldToFilter('status', array('in' => $allowedStatuses));

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                if ($withoutRemindersOnly && $item->getReminder()) {
                    continue;
                }
                
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                    $options = $item->getProductOptions();
                    if (!empty($options) && isset($options['super_product_config']['product_id'])) {
                        $productIds[] = $options['super_product_config']['product_id'];
                    }
                } else {
                    $productIds[] = $item->getProductId();
                }
            }
        }

        array_unique($productIds);
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        /** @var Mage_Catalog_Model_Product_Visibility $productVisibleModel */
        $productVisibleModel = Mage::getModel('catalog/product_visibility');
        $productCollection
            ->addAttributeToFilter('visibility', array('in' => $productVisibleModel->getVisibleInSiteIds()))
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        if (!$showOutOfStockProduct) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
        }
        
        $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
        return $productCollection;
    }
    /**
     * @return bool
     */
    public function isSendingAllowedNow()
    {
        $now = Mage::getModel('core/locale')->storeDate(null,null,true,'yyyy-MM-dd HH:mm:ss');
        $currentHour = (int) $now->get(Zend_Date::HOUR);
        $allowSend = $currentHour > 8 && $currentHour < 20;
        return $allowSend;
    }
}
