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



class Mirasvit_Rma_Helper_Order
{
    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function canCreateCreditmemo($rma, $order)
    {
        $creditModuleInstalled = Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Credit');
        if ($rma->getCreditMemoIds()) {
            foreach ($rma->getCreditMemoIds() as $id) {
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($id);
                if ($creditmemo->getOrderId() == $order->getId()) {
                    return false;
                }
            }
        }

        $resolutionCodes = $this->getConfig()->getPolicyAllowCreditMemoResolutions($order->getStore());
        $allowedResolutions = array();
        foreach ($resolutionCodes as $code) {
            $allowedResolutions[] = Mage::helper('rma')->getResolutionByCode($code)->getId();
        }

        $creditResolution = Mage::helper('rma')->getResolutionByCode('credit');

        if ($creditModuleInstalled) {
            if (!$creditResolution) {
                return false;
            }
            $realPaidAmount = $rma->getStore()->roundPrice($order->getTotalPaid() + $order->getCreditInvoiced());
            $realRefunded = $rma->getStore()->roundPrice($order->getTotalRefunded() + $order->getCreditTotalRefunded());
            if (!$order->canCreditmemo() && abs($realPaidAmount - $realRefunded) < .0001) {
                return false;
            }
        } else if (!$order->canCreditmemo() || !count($allowedResolutions)) {
            return false;
        }

        $haveItems = false;
        foreach (Mage::helper('rma')->getRmaItems($order, $rma) as $item) {
            if (!in_array($item->getResolutionId(), $allowedResolutions)) {
                if ($creditModuleInstalled && $item->getResolutionId() == $creditResolution->getId()) {
                    $haveItems = true;
                }
                continue;
            }

            $haveItems = true;
        }

        return $haveItems;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canReturnToStoreCredit($rma, $order)
    {
        $enabled = $this->getConfig()->getPolicyAllowStoreCreditReturn($rma->getStore());
        if ($creditModuleInstalled = Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Credit') && $enabled) {
            if ($creditResolution = Mage::helper('rma')->getResolutionByCode('credit')) {
                $haveItems = false;
                foreach (Mage::helper('rma')->getRmaItems($order, $rma) as $item) {
                    if ($creditModuleInstalled && $item->getResolutionId() == $creditResolution->getId()) {
                        $haveItems = true;
                    }
                    continue;
                }
                return $haveItems;
            }
        }

        return false;
    }

    /**
     * Sends notification about new order creation
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    protected function sendOrderNotification($order)
    {
        try {
            $order->sendNewOrderEmail();
            $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                ->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
            if ($historyItem) {
                $historyItem->setIsCustomerNotified(1);
                $historyItem->save();
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to send replacement order email.'));
            Mage::logException($e);
        }
    }

    /**
     * Updates order credentials with data from original orders
     * @param Mage_Sales_Model_Order $order
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return void
     */
    protected function updateOrderCredentials($order, $rma)
    {
        $origOrders = $rma->getOrders();
        foreach ($origOrders as $origOrder) {
            if ($origOrder->getIsOffline()) {
                continue;
            }

            $order->setGlobalCurrencyCode($origOrder->getGlobalCurrencyCode())
                ->setBaseCurrencyCode($origOrder->getBaseCurrencyCode())
                ->setStoreCurrencyCode($origOrder->getStoreCurrencyCode())
                ->setOrderCurrencyCode($origOrder->getOrderCurrencyCode());

            // set Billing Address, if customer has no default billing address
            if (!$order->getBillingAddress()) {
                $data = $origOrder->getBillingAddress()->getData();
                unset($data['entity_id']);
                $billingAddress = Mage::getModel('sales/order_address')
                    ->setData($data);
                $order->setBillingAddress($billingAddress);
            }

            if ($origOrder->getShippingAddress()) {
                $data = $origOrder->getShippingAddress()->getData();
                unset($data['entity_id']);
                $shippingAddress = Mage::getModel('sales/order_address')
                    ->setData($data);
                $order->setShippingAddress($shippingAddress)
                    ->setShipping_method('flatrate_flatrate');
            }
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Item $item
     * @return bool
     */
    public function isReplacementAllowed($item)
    {
        $resolution = Mage::getModel('rma/resolution')->load($item->getResolutionId());
        if (in_array($resolution->getCode(),
            Mage::getSingleton('rma/config')->getPolicyAllowReplacementResolutions())) {
            return true;
        }
        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $stockQty
     * @param int $storeId
     * @return void
     */
    public function updateStockQty($product, $stockQty, $storeId)
    {
        if (!($stockItem = $product->getStockItem())) {
            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->assignProduct($product)
                ->setData('stock_id', 1)
                ->setData('store_id', $storeId);
        }

        $stockQty = $stockItem->getQty() - $stockQty;
        $stockItem->setData('qty', $stockQty)
            ->setData('is_in_stock', $stockQty > 0 ? 1 : 0)
            ->setData('manage_stock', 1)
            ->setData('use_config_manage_stock', 0)
            ->save();
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return void
     * @throws Exception
     * @throws Mage_Core_Exception
     * @throws bool
     */
    public function createReplacementOrder($rma)
    {
        if (!$customer = $rma->getCustomer()) {
            throw new Mage_Core_Exception('Replacement Orders available only for registered customers!');
        }

        $transaction = Mage::getModel('core/resource_transaction');
        $storeId = $rma->getStoreId();
        if (!$storeId) {
            $order = $rma->getOrders()->getLastItem();
            if ($order) {
                $storeId = $order->getStore()->getId();
            }
        }
        $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);

        $order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($storeId)
            ->setQuoteId(0)
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomerIsGuest(0)
            ->setCustomer($customer);

        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($storeId)
            ->setCustomerPaymentId(0)
            ->setMethod('purchaseorder')
        ;
        $order->setPayment($orderPayment);

        // Emulate quote (some shipping/packaging software need it) by creating it from order
        $converter = Mage::getModel('sales/convert_order');
        $fakeQuote = $converter->toQuote($order);
        $fakeQuote->save();

        $order->setQuote($fakeQuote);

        // let say, we have 2 products
        //check that your products exists
        //need to add code for configurable products if any
        $subTotal = 0;
        $haveItems = false;
        foreach ($rma->getItemsCollection() as $item) {
            if (!$this->isReplacementAllowed($item)) {
                continue;
            }

            if (!$product = $item->getProduct()) {
                continue;
            };

            $qty = $item->getQtyRequested();

            $this->updateStockQty($product, $qty, $storeId);

            $rowTotal = 0;

            // Get actual tax percent for a product
            $taxCalculation = Mage::getModel('tax/calculation');
            $request = $taxCalculation->getRateRequest(null, null, null, $order->getStore());
            $taxClassId = $product->getTaxClassId();
            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

            $orderItem = Mage::getModel('sales/order_item')
                ->setStoreId($storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(null)
                ->setProductId($product->getId())
                ->setProductType($product->getTypeId())
                ->setQtyBackordered(null)
                ->setTotalQtyOrdered($qty)
                ->setQtyOrdered($qty)
                ->setName($product->getName())
                ->setSku($item->getProductSku())
                ->setWeight($item->getProduct()->getWeight())
                ->setRowWeight($item->getOrderItem()->getRowWeight())
                ->setPrice($product->getPrice())
                ->setBasePrice($product->getPrice())
                ->setOriginalPrice($product->getPrice())
                ->setBaseOriginalPrice($product->getPrice())
                ->setBaseCost(($product->getCost()) ? $product->getCost() : $product->getPrice())
                ->setTaxPercent($percent)
                ->setRowTotal($rowTotal)
                ->setBaseRowTotal($rowTotal);

            $subTotal += $rowTotal;
            $order->addItem($orderItem);
            $haveItems = true;
        }
        if (!$haveItems) {
            throw new Mage_Core_Exception('RMA does not contain valid items with Exchange Resolution');
        }

        $order->setSubtotal($subTotal)
            ->setBaseSubtotal($subTotal)
            ->setGrandTotal($subTotal)
            ->setBaseGrandTotal($subTotal);

        $this->updateOrderCredentials($order, $rma);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));
        $transaction->save();

        //we have fake empty quote here. we need it for this event.
        $quote = Mage::getModel('sales/quote')->setInventoryProcessed(false);
        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        $this->sendOrderNotification($order);
    }

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @param array $data
     *
     * @return Varien_Object
     *
     * @throws Exception
     */
    public function createOfflineOrder($data)
    {
        $order = new Varien_Object();
        $order->setId(Mirasvit_Rma_Model_Config::RMA_OFFLINE_PREFIX.$data['id']);
        $order->setName($data['id']);
        $order->setEntity($data['id']);
        $order->setIncrementId($data['id']);
        $order->setIsOffline(true);
        $order->setStoreId(Mage::app()->getStore()->getId());

        if (!empty($data['customer_id'])) {
            $order->setCustomerId($data['customer_id']);
        }

        if (!empty($data['items'])) {
            $orderItems = $data['items'];
        } else {
            $orderItems = Mage::app()->getRequest()->getParam('items', array());
        }

        if (isset($data['address'])) {
            $order->setOfflineAddress($data['address']);
        }

        $itemCollection = new Varien_Data_Collection();
        if ($orderItems) {
            $orderId = Mirasvit_Rma_Model_Config::RMA_OFFLINE_PREFIX.$data['id'];
            foreach ($orderItems[$orderId] as $itemId => $v) {
                $item = new Varien_Object();
                $item->setData($orderItems[$orderId][$itemId]);
                $item->setId($itemId);
                $item->setName($itemId);
                $item->setOrderItemId($itemId);
                $item->setQtyAvailable($item->getQty());

                $itemCollection->addItem($item);
            }
        }

        $order->setItems($itemCollection);

        return $order;
    }

    /**
     * @param int $orderId
     *
     * @return int
     */
    public function getOrderAvailableDays($orderId)
    {
        $allowedStatuses = $this->getConfig()->getPolicyAllowInStatuses();
        $limitDate = Mage::helper('rma')->getLastReturnGmtDate();

        /** @var Mage_Sales_Model_Resource_Order_Status_History_Collection $collection */
        $collection = Mage::getModel('sales/order_status_history')->getCollection();

        $collection->getSelect()
            ->columns('DATEDIFF(NOW(), main_table.created_at) as days_passed')
            ->where('main_table.parent_id = '.$orderId)
            ->where("main_table.status IN ('".implode("','", $allowedStatuses)."')")
            ->where("main_table.created_at > '".$limitDate."'")
            ->order('main_table.created_at ASC')
        ;

        return Mage::helper('rma')->getReturnPeriod() - $collection->getFirstItem()->getDaysPassed();
    }
}
