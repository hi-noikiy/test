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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Model_Observer_Order extends Mirasvit_Rewards_Model_Observer_Abstract
{
    /**
     * Recalculates points, earned and spent on specific order (quote)
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return void
     */
    protected function refreshPoints($quote)
    {
        if ($quote->getIsPurchaseSave()) {
            return;
        }

        if (!$purchase = Mage::helper('rewards/purchase')->getByQuote($quote)) {
            return;
        }

        if (Mage::helper('core')->isModuleEnabled('Amasty_Shiprestriction')) {
            if (Mage::registry('rewards_was_calculated')) {
                return;
            }
        }
        if (!(Mage::getModel('customer/session')->isLoggedIn() && Mage::getModel('customer/session')->getId())
            && !Mage::app()->getStore()->isAdmin()) {
            $purchase->setSpendPoints(0);
        }
        $purchase->refreshPointsNumber();
        $purchase->save();

        Mage::helper('rewards/referral')->rememberReferal($quote);
    }

    /**
     * Event handler, which is fired ONLY in backend after quote save. Refreshes points.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function quoteAfterSaveBackend($observer)
    {
        $stages = Mage::getSingleton('rewards/config')->getAdvancedDisabledRefreshStages();
        if (in_array(Mirasvit_Rewards_Model_Config::STAGE_QUOTE_SAVE_BACKEND, $stages)) {
            return;
        }

        if (Mage::getSingleton('rewards/config')->getQuoteSaveFlag()) {
            return;
        }
        if (!$quote = $observer->getQuote()) {
            return;
        }
        Mage::getSingleton('rewards/config')->setQuoteSaveFlag(true);
        $this->refreshPoints($quote);
        Mage::getSingleton('rewards/config')->setQuoteSaveFlag(false);
    }

    /**
     * Event handler, which is fired ONLY in frontend after quote save. Refreshes points.
     *
     * @return void
     */
    public function quoteAfterSave()
    {
        if (!$quote = Mage::getModel('checkout/cart')->getQuote()) {
            return $this;
        }

        $stages = Mage::getSingleton('rewards/config')->getAdvancedDisabledRefreshStages();
        if (in_array(Mirasvit_Rewards_Model_Config::STAGE_QUOTE_SAVE_FRONTEND, $stages)) {
            return $this;
        }

        $this->refreshPoints($quote);
    }

    /**
     * Events dispatcher, which is fired ONLY in frontend after quote save. Refreshes points.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function actionPredispatch($observer)
    {
        $uri = $observer->getControllerAction()->getRequest()->getRequestUri();

        // Additional quote calculation exclusion (for Point-of-Sales extensions and so on)
        $exclude = Mage::getSingleton('rewards/config')->getAdvancedExcludeRefreshPath();
        if (trim($exclude) != '') {
            $regexCheck = "/^\/[\s\S]+\/$/";
            if (preg_match($regexCheck, $exclude) && preg_match($exclude, $uri)) {
                return;
            } else if (strpos($uri, $exclude)) {
                return;
            }
        }

        /*if (strpos($uri, 'customer/account/create') !== false &&
            $observer->getControllerAction()->getRequest()->getMethod() != 'POST'
        ) {*/
        if (strpos($uri, 'account/login') !== false) {    
            Mage::helper('rewards/behavior')->displayBehaviourNotification(
                Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SIGNUP);
            return;
        }

        if ($observer->getControllerAction()->getRequest()->getControllerName() == 'product') {
            Mage::helper('rewards/behavior')->displayBehaviourNotification(
                Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REVIEW);
            return;
        }

        if (strpos($uri, 'checkout') === false && strpos($uri, 'ajaxcart') === false) {
            return;
        }
        if (!$quote = Mage::getModel('checkout/cart')->getQuote()) {
            return;
        }
        //this does not calculate quote correctly
        if (strpos($uri, '/checkout/cart/add/') !== false ||
            strpos($uri, '/checkout/cart/updatePost/') !== false ||
            strpos($uri, '/rewards/checkout/applyPointsAitocOnestepcheckout/') !== false
        ) {
            return;
        }

        //this does not calculate quote correctly with firecheckout
        $routeName = $observer->getControllerAction()->getRequest()->getRouteName();
        if ($routeName === 'rewards'
            || $routeName === 'amscheckoutfront'
            || $routeName === 'firecheckout'
        ) {
            return;
        }

        //this does not calculate quote correctly with gomage
        if (strpos($uri, '/gomage_checkout/onepage/save/') !== false) {
            return;
        }

        //this does not calculate quote correctly with simplecheckout
        if (strpos($uri, '/simplecheckout/cart/add/') !== false) {
            return;
        }

        $stages = Mage::getSingleton('rewards/config')->getAdvancedDisabledRefreshStages();
        if (in_array(Mirasvit_Rewards_Model_Config::STAGE_ORDER_PREDISPATCH, $stages)) {
            return;
        }

        if (
            Mage::helper('core')->isModuleEnabled('AW_Onestepcheckout') &&
            (
                strpos($uri, '/onestepcheckout/ajax') === false &&
                strpos($uri, '/rewards/checkout/applyPointsGoMageLightcheckout') === false
            )
        ) {
            return;
        }
        if (Mage::helper('core')->isModuleEnabled('Amasty_Shiprestriction')) {
            if (Mage::registry('rewards_was_calculated')) {
                return;
            }

            Mage::register('rewards_was_calculated', true, true);
        }
        if (Mage::helper('core')->isModuleEnabled('Lotusbreath_OneStepCheckout') &&
            strpos($uri, 'saveStep') !== false
        ) {
            return;
        }
        $this->refreshPoints($quote);
    }

    /**
     * Order placement handler. Applies spent points.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function orderPlaceAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->_isOrderPaidNow($order)) {
            if ($order->getCustomerId()) {
                ;
                Mage::helper('rewards/balance_order')->spendOrderPoints($order);
            }
        }
    }

    /**
     * Order cancelling handler. Restores spent points.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function orderCancelAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->_isOrderPaidNow($order)) {
            if ($order->getCustomerId()) {
                Mage::helper('rewards/balance_order')->restoreSpendPoints($order);
                Mage::helper('rewards/balance_order')->cancelEarnedPoints($order, null);
            }
        }
    }

    /**
     * Checkout success handler. Adds notification for points rolling to the customer account.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function checkoutSuccess($observer)
    {
        $session = Mage::getSingleton('checkout/type_onepage')->getCheckout();
        $orderId = $session->getLastOrderId();
        if (!$session->getLastSuccessQuoteId() || !$orderId) {
            return;
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        //$this->addPointsNotifications($order);
    }

    /**
     * Displays notification about points, awarded and spent for an order placement.
     *
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function addPointsNotifications($order)
    {
        if (!$order->getCustomerId()) {
            return;
        }

        $quote = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('entity_id', $order->getQuoteId())
                ->getFirstItem(); //we need this for correct work if we create orders via backend
        $totalEarnedPoints = Mage::helper('rewards/balance_earn')->getPointsEarned($quote);
        $purchase = Mage::helper('rewards/purchase')->getByOrder($order);
        $totalSpendPoints = $purchase->getPointsNumber();

        if ($totalEarnedPoints && $totalSpendPoints) {
            $this->addNotificationMessage(Mage::helper('rewards')->__('You earned %s and spent %s for this order.',
                Mage::helper('rewards')->formatPoints($totalEarnedPoints),
                Mage::helper('rewards')->formatPoints($totalSpendPoints)));
        } elseif ($totalSpendPoints) {
            $this->addNotificationMessage(Mage::helper('rewards')->__('You spent %s for this order.',
                Mage::helper('rewards')->formatPoints($totalSpendPoints)));
        } elseif ($totalEarnedPoints) {
            $this->addNotificationMessage(Mage::helper('rewards')->__('You earned %s for this order.',
                Mage::helper('rewards')->formatPoints($totalEarnedPoints)));
        }
        if ($totalEarnedPoints) {
            $this->addNotificationMessage(Mage::helper('rewards')->__('Earned points will be enrolled to your' .
                ' account after we finish processing your order.'));
        }
    }

    /**
     * Adds notification message to the current session
     *
     * @param string $message
     * @return void
     */
    private function addNotificationMessage($message)
    {
        $message = Mage::getSingleton('core/message')->success($message);
        Mage::getSingleton('core/session')->addMessage($message);
    }

    /**
     * Checks, whether order was paid.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _isOrderPaidNow($order)
    {
        if (!Mage::registry('mst_ordercompleted_done')) {
            Mage::register('mst_ordercompleted_done', true);

            return true;
        }

        return false;
    }

    /**
     * Invoice creation handler.
     * Can roll points to the customer's balance, if order is paid and "Allow Earning After Invoice" is set.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function afterInvoiceSave($observer)
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID) {
            return;
        }

        // Add rule processing
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_ORDER, $customer,
            Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId(),
            $order->getIncrementId());

        if ($order && $this->getConfig()->getGeneralIsEarnAfterInvoice() && $order->getPayment()->getMethod() != "cashondelivery") { //cod escape adding points before delivery
            $this->earnOrderPoints($order);
        }

        // We need this, because backend orders became paid only after invoice,
        // and function orderPlaceAfter is not applicable
        if (!$order->getRemoteIp()) {
            Mage::helper('rewards/balance_order')->spendOrderPoints($order);
        }
    }

    /**
     * Shipment creation handler.
     * Can roll points to the customer's balance, if order is paid and "Allow Earning After Shipment" is set.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return Mirasvit_Rewards_Model_Observer_Order
     */
    public function afterShipmentSave($observer)
    {
        $object = $observer->getObject();
        if (!($object && ($object instanceof Mage_Sales_Model_Order_Shipment))) {
            return $this;
        }

        $order = Mage::getModel('sales/order')->load((int) $object->getOrderId());

        // Add rule processing
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_ORDER, $customer,
            Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId(),
            $order->getIncrementId());

        if ($order && $this->getConfig()->getGeneralIsEarnAfterShipment()) {
            $this->earnOrderPoints($order);
        }

        return $this;
    }

    /**
     * Order saving handler.
     * Can roll points to the customer's balance, if order is paid and current status is
     * in "Approve earned points if order has status" array.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function orderSaveAfter($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return;
        }
        $status = $order->getStatus();

        if (in_array($status, $this->getConfig()->getGeneralEarnInStatuses()) && $order->getPayment()->getMethod() != "cashondelivery") {
            $this->earnOrderPoints($order);
        }

        if(($status == "complete_delivered" || $status == "processing_delivered") && $order->getPayment()->getMethod() == "cashondelivery") {//cod add points after delivery
            $this->earnOrderPoints($order);
        }
        if(($status == "cancel" || $status == "canceled") && $order->getPayment()->getMethod() == "cashondelivery") {
            Mage::helper('rewards/balance_order')->cancelEarnedPointsWithNoCreditMemo($order);
        }
    }

    /**
     * Rolls points for the order to the customer's balance.
     *
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    protected function earnOrderPoints($order)
    {
        if ($order->getCustomerId()) {
            Mage::helper('rewards/balance_order')->earnOrderPoints($order);
        }
        Mage::helper('rewards/referral')->processReferralOrder($order);
    }

    /**
     * Refund save handler.
     * Can restore points to the customer's balance, if "Restore points after refund" is set.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function afterRefundSave($observer)
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
        if (!$creditMemo = $observer->getEvent()->getCreditmemo()) {
            return;
        }
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($creditMemo->getOrderId());
        if ($this->getConfig()->getGeneralIsCancelAfterRefund()) {
            Mage::helper('rewards/balance_order')->cancelEarnedPoints($order, $creditMemo);
        }

        if ($this->getConfig()->getGeneralIsRestoreAfterRefund()) {
            Mage::helper('rewards/balance_order')->restoreSpendPoints($order, $creditMemo);
        }
    }

    /**
     * @param Mage_Core_Model_Observer $observer
     * @return $this
     */
    public function onCreateProcessData($observer)
    {
        $quote   = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();

        if (isset($request['use_points']) && isset($request['points_amount'])) {
            $quote->setUseCredit(false);
            /** @var Mirasvit_Rewards_Model_Purchase $purchase */
            $purchase = Mage::helper('rewards/purchase')->getByQuote($quote);
            if ($purchase) {
                $purchase->setSpendPoints($request['points_amount'])
                    ->refreshPointsNumber(true)
                    ->save();
            }
        }
        if (Mage::helper('core')->isModuleEnabled('Mirasvit_Credit')) {
            $quote->setCreditCollected(false)->setTotalsCollectedFlag(false);

            /** @var Mirasvit_Credit_Model_Observer_Order $creditObserver */
            $creditObserver = Mage::getModel('credit/observer_order');
            $creditObserver->applyCreateProcessData($observer, $quote, $request);
        }

        return $this;
    }
}