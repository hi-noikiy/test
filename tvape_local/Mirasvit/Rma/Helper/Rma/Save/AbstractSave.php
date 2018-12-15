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



abstract class Mirasvit_Rma_Helper_Rma_Save_AbstractSave
{
    /**
     * Save function for backend.
     * Variables:
     * $data = array
     *  rma_id
     *  increment_id
     *  customer_id
     *  user_id
     *  status_id
     * ...
     * $items = array
     *  order_id => array
     *      item_id => array
     *          order_id
     *          item_id
     *          order_item_id
     *          qty_requested
     *          reason_id
     *          condition_id
     *          resolution_id
     * ...
     * $offlineItems = array
     *  order_id => array
     *      item_id => array
     *          offline_order_id Or receipt_number
     *          offline_item_id Or unset
     *          qty_requested
     *          reason_id
     *          condition_id
     *          resolution_id
     *          rma_id Or unset
     * ...
     * @param Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor
     *
     * @return Mirasvit_Rma_Model_Rma
     *
     * @throws Exception
     */
    public function createOrUpdateRma(Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor)
    {
        $items = $dataProcessor->getItems();
        $offlineItems = $dataProcessor->getOfflineItems();
        $data = $dataProcessor->getRmaData();

        $rma = Mage::getModel('rma/rma');
        if (isset($data['rma_id']) && $data['rma_id']) {
            $rma->load((int) $data['rma_id']);
            $rmaIsNew = false;
        } else {
            unset($data['rma_id']);
            $rmaIsNew = true;
        }
        if (isset($data['street2']) && $data['street2'] != '') {
            $data['street'] .= "\n".$data['street2'];
            unset($data['street2']);
        }

        // Hack for custom fields of date format
        $customDates = Mage::getModel('rma/field')->getCollection()
            ->addFieldToFilter('type', 'date')
            ->addFieldToFilter('is_product', false)
            ->addFieldToFilter('is_active', true);
        foreach ($customDates as $customDate) {
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $data[$customDate->getCode()] = $this->_formatDateForSave($data[$customDate->getCode()], $format);
        }

        $rma->addData($data);
        $this->setCustomerData($rma);
        $this->setUserData($rma);

        if ($rma->getStatusId() != $rma->getOrigData('status_id') && $rma->getStatus()->getCustomerMessage()) {
            $rma->setIsAdminRead(true);
        }

        if (!$rma->getCustomerId()) {
            $rma->unsetData('customer_id');
        }

        $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
        if (!$customer->getId()) {
            $rma->unsetData('customer_id');
        }

        // Additional processing to get proper numbers for offline orders
        $offlineNumbers = array();
        foreach (array_keys($offlineItems) as $offlineId) {
            $offlineNumbers[] = Mage::getModel('rma/offline_order')->load($offlineId)->getReceiptNumber();
        }

        $orderIds = array_merge(array_keys($items), $offlineNumbers);
        $rma->setData('orders', $orderIds);
        $this->setRmaAddress($rma);

        if (!$rma->getStoreId()) {
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getStore()) {
                    $rma->setStoreId($order->getStore()->getId());
                    break;
                } else {
                    if ($customer = $rma->getCustomer()) {
                        $rma->setStoreId($customer->getStoreId());
                    } else {
                        $rma->setStoreId(Mage::app()->getStore()->getId());
                    }
                }
            }
        }
        $rma->save();

        Mage::helper('mstcore/attachment')->saveAttachment('rma_return_label', $rma->getId(), 'return_label');

        $this->saveItems($rma, $items);
        $this->saveOfflineItems($rma, $offlineItems);

        if ($rmaIsNew && $rma->getTicketId()) {
            $this->closeTicketByRma($rma);
        }

        $this->addComment($data, $rma);

        Mage::dispatchEvent('mst_rma_changed', array('rma'=>$rma));

        return $rma;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma       $rma
     * @return void
     */
    protected function setRmaAddress($rma)
    {
        $customer = $rma->getCustomer();

        preg_match_all('/ebay guest/ims', $rma->getCustomer()->getFirstname(), $matches);
        if (count($matches)) {
            // If it is predefined eBay customer, pick up Invoice, and get address from there
            $orderIds = $rma->getData('orders');
            $baseOrder = null;
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId()) {
                    $baseOrder = $order;
                    break;
                }
            }
            if ($baseOrder) {
                $invoice = $baseOrder->getInvoiceCollection()->getFirstItem();
                $address = Mage::getModel('sales/order_address')->load($invoice->getBillingAddressId());
            } else {
                $address = $customer->getDefaultBillingAddress();
            }
        } else {
            $address = $customer->getDefaultBillingAddress();
        }

        if ($address) {
            $this->setRmaAddressData($rma, $address);
        }
    }

    /**
     * Sets RMA address properties from Address object.
     *
     * @param Mirasvit_Rma_Model_Rma      $rma
     * @param Mage_Customer_Model_Address $address
     *
     * @return Mirasvit_Rma_Model_Rma
     */
    public function setRmaAddressData($rma, $address)
    {
        $rma
            ->setFirstname($address->getFirstname())
            ->setLastname($address->getLastname())
            ->setCompany($address->getCompany())
            ->setTelephone($address->getTelephone())
            ->setStreet(implode("\n", $address->getStreet()))
            ->setCity($address->getCity())
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setRegion($address->getRegion())
            ->setPostcode($address->getPostcode());

        return $rma;
    }
    /**
     * Hook. Dont remove it.
     *
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return void
     */
    protected abstract function setCustomerData($rma);

    /**
     * Hook. Dont remove it.
     *
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return void
     */
    protected abstract function setUserData($rma);


    /**
     * @param array                  $data
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return void
     */
    protected abstract function addComment($data, $rma);

    /**
     * Returns current RMA Configuration object.
     *
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param array                  $items
     *
     * @throws Exception
     * @return void
     */
    protected function saveItems($rma, $items)
    {
        foreach ($items as $orderId => $itemList) {
            foreach ($itemList as $item) {
                $rmaItem = Mage::getModel('rma/item');

                if (isset($item['item_id']) && $item['item_id']) {
                    $rmaItem->load((int) $item['item_id']);
                } else {
                    unset($item['item_id']);
                }

                $rmaItem->addData($item)->setRmaId($rma->getId());

                $order = Mage::getModel('sales/order')->load((int) $orderId);
                $rmaItem->setOrderId($order->getId());
                $orderItem = Mage::getModel('sales/order_item')->load((int) $item['order_item_id']);
                $rmaItem->initFromOrderItem($orderItem);

                $rmaItem->save();
            }
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param array                  $items
     *
     * @throws Exception
     * @return void
     */
    protected function saveOfflineItems($rma, $items)
    {
        foreach ($items as $orderId => $itemList) {
            foreach ($itemList as $item) {
                $rmaItem = Mage::getModel('rma/offline_item');

                if (isset($item['offline_item_id']) && $item['offline_item_id']) {
                    $rmaItem->load((int) $item['offline_item_id']);
                } else {
                    unset($item['offline_item_id']);
                }
                $rmaItem->addData($item)->setRmaId($rma->getId());
                if (isset($item['receipt_number'])) {
                    $offlineOrder = Mage::helper('rma/offlineOrder')->getOfflineOrder(
                        $rma->getCustomerId(), $item['receipt_number']
                    );
                    $rmaItem->setOfflineOrderId($offlineOrder->getId());
                }
                $rmaItem->save();
            }
        }
    }

    /**
     * Prepare date for save in DB.
     *
     * String format used from input fields (all date input fields need apply locale settings)
     * Int value can be declared in code (this meen whot we use valid date)
     *
     * @param string $date
     * @param string $format
     *
     * @return string | bool
     */
    protected function _formatDateForSave($date, $format)
    {
        if (empty($date)) {
            return false;
        }

        if ($format) {
            $date = Mage::app()->getLocale()->date($date,
                $format,
                null, false
            );
        } elseif (preg_match('/^[0-9]+$/', $date)) {
            // unix timestamp given - simply instantiate date object
            $date = new Zend_Date((int) $date);
        } elseif (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) {
            // international format
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        } else {
            // parse this date in current locale, do not apply GMT offset
            $date = Mage::app()->getLocale()->date($date,
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                null, false
            );
        }

        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    //    /**
    //     * Updates customer shipping address from billing address or order, if there is no default.
    //     *
    //     * @param Mage_Customer_Model_Customer $customer
    //     * @param Mage_Sales_Model_Order       $order
    //     */
    //    protected function updateCustomerAddress($customer, $order)
    //    {
    //        if (!$customer->getAddresses()) {
    //            return;
    //        }
    //        if (!$customer->getDefaultShippingAddress()) {
    //            if (!$customer->getDefaultBillingAddress()) {
    //                $address = Mage::getModel('customer/address');
    //                $orderAddress = $order->getShippingAddress()->getData();
    //                unset($orderAddress['entity_id'],
    //                    $orderAddress['parent_id'],
    //                    $orderAddress['customer_id'],
    //                    $orderAddress['customer_address_id'],
    //                    $orderAddress['quote_address_id']);
    //                $address->setData($orderAddress);
    //                $address->setParentId($customer->getId());
    //                $address->setIsDefaultBilling(true);
    //                $address->setIsDefaultShipping(true);
    //                $address->save();
    //                $customer->addAddress($address);
    //                $customer->save();
    //            } else {
    //                $address = $customer->getDefaultBillingAddress();
    //                $address->setIsDefaultShipping(true);
    //                $address->save();
    //                $customer->save();
    //            }
    //        }
    //    }


    /**
     * Closes ticket by RMA.
     *
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return void
     */
    public function closeTicketByRma($rma)
    {
        $ticket = Mage::getModel('helpdesk/ticket')->load($rma->getTicketId());
        $ticket->addMessage(Mage::helper('rma')->__('Ticket was converted to the RMA #%s', $rma->getIncrementId()),
            false,
            $rma->getUser(), Mirasvit_Helpdesk_Model_Config::USER, Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL);
        $ticket->close();
    }
}