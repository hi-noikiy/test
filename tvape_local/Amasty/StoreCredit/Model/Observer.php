<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Observer
{

    public function prepareCustomerBalanceSave($observer)
    {
        $customer = $observer->getCustomer();
        if ($data = $observer->getRequest()->getPost('amstcred')) {
            $customer->setAmstcredData($data);
        }
    }


    public function customerSaveAfter($observer)
    {
        if ($data = $observer->getCustomer()->getAmstcredData()) {
            if (Mage::app()->isSingleStoreMode()) {
                $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            } else {
                $websiteId = $observer->getCustomer()->getWebsiteId();
            }
            if (!empty($data['amount_delta'])) {
                $balance = Mage::getModel('amstcred/balance')
                    ->setCustomer($observer->getCustomer())
                    ->setWebsiteId(
                        isset($data['website_id']) ? $data['website_id'] : $websiteId
                    )
                    ->setStoreId(
                        $data['store_id']
                    )
                    ->setAmountDelta($data['amount_delta'])
                    ->setComment($data['comment'])
                    ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_ADMIN);
                $balance->save();
            }
        }
    }

    public function prepareCustomerGrid($observer)
    {
        $block = $observer->getBlock();
        $customerGridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
        if ($customerGridClass != get_class($block)) {
            return $this;
        }


        $block->addColumnAfter('amstcred_balance', array(
            'header' => Mage::helper('amstcred')->__('Store Credit'),
            'index' => 'amstcred_balance',
            'type' => 'price',
            'currency_code' => 'USD',
            'renderer' => 'amstcred/adminhtml_renderer_currency',
            'filter_condition_callback' => array($this, 'filterAmstcredBalanceCallback'),
        ), 'website_id');
    }


    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $massactionClass = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $customerGridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
        if ($massactionClass == get_class($block) && $customerGridClass == get_class($block->getParentBlock())) {
            $block->addItem('amstcred_update_balance', array(
                'label' => Mage::helper('amstcred')->__('Update Credit'),
                'url' => Mage::getUrl('*/amstcred_customer/massUpdateCredit', array('' => '')),
                'additional' => array(
                    'amstcred_amount_delta' => array(
                        'name' => 'amstcred_amount_delta',
                        'type' => 'text',
                        'class' => 'required-entry',
                        'label' => Mage::helper('amstcred')->__('By'),
                    ),
                    'amstcred_store_id' => array(
                        'name' => 'amstcred_store_id',
                        'type' => 'select',
                        'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
                        'class' => 'required-entry',
                        'label' => Mage::helper('amstcred')->__('Store'),
                    ),

                ),
                //'confirm' => Mage::helper('amstcred')->__('Are you sure?'),
            ));
        }

        return $this;
    }


    /**
     * @param Mage_Customer_Model_Resource_Customer_Collection $collection
     * @param $column
     */
    public function filterAmstcredBalanceCallback($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $condition = $collection->getConnection()->prepareSqlCondition('at_amstcred_balance.amount', $value);
        if (empty($value["from"])) {
            $condition .= "OR at_amstcred_balance.amount IS NULL";
        }
        $collection->getSelect()->where($condition);

    }

    public function addSelectCustomerCollection($observer)
    {
        $collection = $observer->getCollection();
        if (!$collection instanceof Mage_Customer_Model_Resource_Customer_Collection) {
            return $this;
        }

        $collection->joinField(
            'amstcred_balance',
            Mage::getResourceModel('amstcred/balance')->getTable('amstcred/customer_balance'),
            'amount',
            'customer_id = entity_id',
            'at_amstcred_balance.website_id = e.website_id',
            'left'
        );
    }


    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = array(
            'amstcred_price_type',
            'amstcred_price_percent',
        );

        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);

        return $this;
    }


    public function appendAdditionalData(Varien_Event_Observer $observer)
    {
        /* @var $orderItem Mage_Sales_Model_Order_Item */
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $keys = array(
            'amstcred_amount',
        );
        $productOptions = $orderItem->getProductOptions();
        foreach ($keys as $key) {
            if ($option = $quoteItem->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }


        $orderItem->setProductOptions($productOptions);

        return $this;
    }


    public function chargeStoreCredit(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $loadedInvoices = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() != Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit::TYPE_STORECREDIT_PRODUCT) {
                continue;
            }

            $qty = 0;
            $options = $item->getProductOptions();

            $paidInvoiceItems = (isset($options['amstcred_paid_invoice_items'])
                ? $options['amstcred_paid_invoice_items']
                : array());

            $invoiceItemCollection = Mage::getResourceModel('sales/order_invoice_item_collection')
                ->addFieldToFilter('order_item_id', $item->getId());

            foreach ($invoiceItemCollection as $invoiceItem) {
                $invoiceId = $invoiceItem->getParentId();
                if (isset($loadedInvoices[$invoiceId])) {
                    $invoice = $loadedInvoices[$invoiceId];
                } else {
                    $invoice = Mage::getModel('sales/order_invoice')
                        ->load($invoiceId);
                    $loadedInvoices[$invoiceId] = $invoice;
                }

                if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID &&
                    !in_array($invoiceItem->getId(), $paidInvoiceItems)
                ) {
                    $qty += $invoiceItem->getQty();
                    $paidInvoiceItems[] = $invoiceItem->getId();
                }
            }
            $options['amstcred_paid_invoice_items'] = $paidInvoiceItems;


            if ($qty > 0) {
                $amount = (isset($options['amstcred_amount'])) ? $options['amstcred_amount'] : 0;
                $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                } else {
                    $customerId = null;
                }


                $balance = Mage::getModel('amstcred/balance')
                    ->setCustomerId($order->getCustomerId())
                    ->setWebsiteId(
                        $websiteId
                    )
                    ->setAmountDelta($amount * $qty)
                    //->setComment($data['comment'])
                    ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_PURCHASE)
                    ->setActionData($order->getIncrementId());
                $balance->save();

                $item->setProductOptions($options);
                $item->save();
            }
        }

        return $this;
    }


    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return;
        }

        $input = $observer->getEvent()->getInput();
        $payment = $observer->getEvent()->getPayment();
        $this->_importPaymentData($payment->getQuote(), $input, $input->getAmstcredUseCustomerBalance());
    }


    protected function _importPaymentData($quote, $payment, $shouldUseBalance)
    {
        $store = Mage::app()->getStore($quote->getStoreId());

        if (!$quote || !$quote->getCustomerId()
            || $quote->getBaseGrandTotal() + $quote->getBaseAmstcredAmountUsed() <= 0
        ) {
            return;
        }
        $quote->setAmstcredUseCustomerBalance($shouldUseBalance);
        if ($shouldUseBalance) {
            $balance = Mage::getModel('amstcred/balance')
                ->setCustomerId($quote->getCustomerId())
                ->setWebsiteId($store->getWebsiteId())
                ->loadByCustomer();
            if ($balance) {
                $quote->setAmstcredCustomerBalanceInstance($balance);
                if (!$payment->getMethod()) {
                    $payment->setMethod('free');
                }
            } else {
                $quote->setAmstcredUseCustomerBalance(false);
            }
        }
        return $this;
    }


    public function togglePaymentMethods($observer)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $balance = $quote->getAmstcredCustomerBalanceInstance();
        if (!$balance) {
            return;
        }

        // disable all payment methods and enable only Zero Subtotal Checkout

        if ($quote->getAmstcredUseCustomerBalance() && $balance->getAmount() >=
            ((float)$quote->getBaseGrandTotal() + (float)$quote->getBaseAmstcredAmountUsed())
        ) {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = $paymentMethod === 'free' && empty($result->isDeniedInConfig);
        }
    }


    public function processOrderPlace(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return;
        }
        $quote = $observer->getEvent()->getQuote();
        if (!Mage::helper('amstcred')->isAllowedStoreCredit($quote)) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseAmstcredAmount() > 0) {
            $this->_checkStoreCreditBalance($order);

            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            $balance = Mage::getModel('amstcred/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId(
                    $websiteId
                )
                ->setAmountDelta(-$order->getBaseAmstcredAmount())
                //->setComment($data['comment'])
                ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_PAY_ORDER)
                ->setActionData($order->getIncrementId());
            $balance->save();
        }

        return $this;
    }


    protected function _checkStoreCreditBalance(Mage_Sales_Model_Order $order)
    {
        if ($order->getBaseAmstcredAmount() > 0) {
            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            $balance = Mage::getModel('amstcred/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->loadByCustomer()
                ->getAmount();

            if (($order->getBaseAmstcredAmount() - $balance) >= 0.0001) {
                Mage::getSingleton('checkout/type_onepage')
                    ->getCheckout()
                    ->setUpdateSection('payment-method')
                    ->setGotoSection('payment');

                Mage::throwException(
                    Mage::helper('amstcred')->__('Not enough Store Credit Amount to complete this Order.')
                );
            }
        }

        return $this;
    }


    public function revertStoreCredit(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            $this->_revertStoreCreditForOrder($order);
        }

        return $this;
    }

    public function revertStoreCreditForAllOrders(Varien_Event_Observer $observer)
    {
        $orders = $observer->getEvent()->getOrders();

        foreach ($orders as $order) {
            $this->_revertStoreCreditForOrder($order);
        }

        return $this;
    }


    protected function _revertStoreCreditForOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getCustomerId() || !$order->getBaseAmstcredAmount()) {
            return $this;
        }


        $balance = Mage::getModel('amstcred/balance')
            ->setCustomerId($order->getCustomerId())
            ->setWebsiteId(
                Mage::app()->getStore($order->getStoreId())->getWebsiteId()
            )
            ->setAmountDelta($order->getBaseAmstcredAmount())
            //->setComment($data['comment'])
            ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_REFUND_ORDER)
            ->setActionData($order->getIncrementId());
        $order->setBaseAmstcredAmountRefunded($order->getBaseAmstcredAmount())->setAmstcredAmountRefunded($order->getAmstcredAmount());
        $balance->save();

        return $this;
    }

    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setAmstcredBalanceCollected(false);
    }


    public function quoteMergeAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getAmstcredUseCustomerBalance()) {
            $quote->setAmstcredUseCustomerBalance($source->getAmstcredUseCustomerBalance());
        }
    }


    public function increaseOrderInvoicedAmount(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();


        if ($invoice->getOrigData() === null && $invoice->getBaseAmstcredAmount()) {
            $order->setBaseAmstcredAmountInvoiced(
                $order->getBaseAmstcredAmountInvoiced() + $invoice->getBaseAmstcredAmount()
            );
            $order->setAmstcredAmountInvoiced(
                $order->getAmstcredAmountInvoiced() + $invoice->getAmstcredAmount()
            );
        }

        $order->getResource()->saveAttribute($order, 'base_amstcred_amount_invoiced');
        $order->getResource()->saveAttribute($order, 'amstcred_amount_invoiced');
        return $this;
    }

    public function customerRegisterAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getCustomer();
        $balanceSend = Mage::getModel('amstcred/balanceSend')->loadByEmailAndWebsite($customer->getEmail(), $customer->getWebsiteId());

        if ($balanceSend->getId()) {
            $sender = Mage::getModel('customer/customer')
                ->setWebsiteId($balanceSend->getWebsiteId())->load($balanceSend->getSenderId());

            $recipientBalance = Mage::getModel('amstcred/balance')->setCustomer($customer)->loadByCustomer();
            $recipientBalance->setAmountDelta($balanceSend->getAmount())
                ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_USER)
                ->setActionData($sender->getName())
                ->save();
            $balanceSend->setRecipientId($customer->getId())->setIsRedeemed(1)->save();
        }

        return $this;
    }


    public function addPaypalCustomerBalanceItem(Varien_Event_Observer $observer)
    {
        $paypalCart = $observer->getEvent()->getPaypalCart();
        if ($paypalCart) {
            $salesEntity = $paypalCart->getSalesEntity();
            if ($salesEntity instanceof Mage_Sales_Model_Quote) {
                $balanceField = 'base_amstcred_amount_used';
            } elseif ($salesEntity instanceof Mage_Sales_Model_Order) {
                $balanceField = 'base_amstcred_amount';
            } else {
                return;
            }

            $value = abs($salesEntity->getDataUsingMethod($balanceField));
            if ($value > 0.0001) {
                $paypalCart->updateTotal(
                    Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,
                    (float)$value,
                    Mage::helper('amstcred')->__(
                        'Store Credit (%s)',
                        Mage::app()->getStore()->convertPrice($value, true, false)
                    )
                );
            }
        }
    }


    public function salesOrderLoadAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED
        ) {
            return $this;
        }

        if ($order->getAmstcredAmountInvoiced() - $order->getAmstcredAmountRefunded() >= 0.0001) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }


    public function processOrderCreationData(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return $this;
        }
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();
        if (isset($request['payment']) && isset($request['payment']['amstcred_use_customer_balance'])) {
            $this->_importPaymentData($quote, $quote->getPayment(),
                (bool)(int)$request['payment']['amstcred_use_customer_balance']);
        }
    }


    public function catalogProductCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            $collection = $observer->getCollection();
            $collection->addFieldToFilter('type_id', array('neq' => Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit::TYPE_STORECREDIT_PRODUCT));
        }

    }

    public function creditmemoDataImport(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $request->getParam('creditmemo');

        if (isset($input['refund_amstcred_enable']) && isset($input['refund_amstcred_amount'])) {
            $enable = $input['refund_amstcred_enable'];
            $amount = $input['refund_amstcred_amount'];
            if ($enable && is_numeric($amount)) {
                $amount = max(0, min($creditmemo->getBaseGrandTotal(), $amount));
                if ($amount) {
                    $baseAmount = $creditmemo->getStore()->roundPrice($amount);
                    $amount = $creditmemo->getStore()->roundPrice(
                        $baseAmount * $creditmemo->getOrder()->getStoreToOrderRate()
                    );
                    $creditmemo->setBaseAmAmountTotalRefunded($baseAmount);
                    $creditmemo->setAmAmountTotalRefunded($amount);
                    $creditmemo->setAmstcredRefundFlag(true);
                    $creditmemo->setPaymentRefundDisallowed(false);
                }
            }
        }

        return $this;
    }


    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();


        if ((float)(string)$creditmemo->getAmAmountTotalRefunded() > (float)(string)$creditmemo->getGrandTotal()) {
            Mage::throwException(Mage::helper('amstcred')->__('Store credit amount cannot exceed order amount.'));
        }
        //doing actual refund to customer balance if user have submitted refund form
        if ($creditmemo->getAmstcredRefundFlag() && $creditmemo->getBaseAmAmountTotalRefunded()) {
            $order->setBaseAmAmountTotalRefunded(
                $order->getBaseAmAmountTotalRefunded() + $creditmemo->getBaseAmAmountTotalRefunded()
            );
            $order->setAmAmountTotalRefunded(
                $order->getAmAmountTotalRefunded() + $creditmemo->getAmAmountTotalRefunded()
            );

            $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

            Mage::getModel('amstcred/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta($creditmemo->getBaseAmAmountTotalRefunded())
                ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_REFUND_ORDER)
                ->setActionData($order->getIncrementId())
                ->save();
        }

        return $this;
    }


    public function refund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

        if ($creditmemo->getBaseAmstcredAmount()) {
            Mage::getModel('amstcred/balance')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAmountDelta($creditmemo->getBaseAmstcredAmount())
                ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_REFUND_ORDER)
                ->setActionData($order->getIncrementId())
                ->save();

            $order->setBaseAmstcredAmountRefunded(
                $order->getBaseAmstcredAmountRefunded() + $creditmemo->getBaseAmstcredAmount()
            );
            $order->setAmstcredAmountRefunded($order->getAmstcredAmountRefunded() + $creditmemo->getAmstcredAmount());
        }

        return $this;
    }

    public function onCustomerSaveBefore(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->isObjectNew()
        && Mage::getStoreConfig('amstcred/email/subscribe_new_customer')) {
            $customer->setAmstcredAutomaticSubscribe(true);
        }
    }

    public function onCustomerSaveAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getAmstcredAutomaticSubscribe()) {
            if (Mage::app()->isSingleStoreMode()) {
                $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            } else {
                $websiteId = $observer->getCustomer()->getWebsiteId();
            }
            Mage::getModel('amstcred/balance')
                ->setWebsiteId($websiteId)
                ->setCustomerId($customer->getId())
                ->loadByCustomer()
                ->setSubscribeUpdates(true)
                ->save();
        }
    }
}
