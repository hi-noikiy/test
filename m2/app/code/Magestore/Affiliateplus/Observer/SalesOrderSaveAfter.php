<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Model\Transaction;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class SalesOrderSaveAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $order = $observer['order'];
        $storeId = $order->getStoreId();
        // Create transaction for the order
        if(!$this->_isExistedTransactionByOrder($order)){
            if ($this->_helper->isAdmin()) {
                $this->_objectManager->create('Magestore\Affiliateplus\Observer\Frontend\SalesOrderPlaceAfter')
                    ->saveDiscountToOrder($order);
            }
            $this->_salesOrderPlaceAfter($order);
        }

        // Return money on affiliate balance
        if ($order->getData('state') == \Magento\Sales\Model\Order::STATE_CANCELED) {
            $this->_returnMoneyToAffiliateBalance($order);
        }

        $configOrderStatus = $this->_helperConfig->getCommissionConfig('updatebalance_orderstatus', $storeId);
        $configOrderStatus = $configOrderStatus ? $configOrderStatus : \Magento\Sales\Model\Order::STATE_PROCESSING;

        if ($order->getStatus() == $configOrderStatus) {
            $transaction = $this->_getTransactionModel()->load($order->getIncrementId(), 'order_number');
            // Complete Transaction or hold transaction
            if ($this->_helperConfig->getCommissionConfig('holding_period', $storeId)) {
                return $transaction->hold();
            }
            return $transaction->complete();
        }
        $cancelStatus = explode(',', $this->_helperConfig->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        if (in_array($order->getStatus(), $cancelStatus)) {
            $transaction = $this->_getTransactionModel()->load($order->getIncrementId(), 'order_number');
            // Cancel Transaction
            return $transaction->cancel();
        }

        if ($this->_helper->isAdmin()){
            $actionName = $this->_request->getActionName();
            $controllerName = $this->_requestHttp->getControllerName();
            if (($actionName == 'cancel' && $controllerName == 'order')
                || ($actionName == 'save' && $controllerName == 'order_invoice')
                || ($actionName == 'save' && $controllerName == 'order_shipment')
            ){
                return $this;
            }

//            Create new order in back-end using coupon code
//            $this->_createTransactionInBackend($order);
        }
    }

    /**
     * @param $order
     */
    protected function _returnMoneyToAffiliateBalance($order) {
        $paymentMethod = $this->_getPaymentCreditModel()->load($order->getId(), 'order_id');
        if ($paymentMethod->getId() && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0) {
            $payment = $this->_getPaymentModel()->load($paymentMethod->getPaymentId())
                ->setData('payment', $paymentMethod);
            $account = $payment->getAffiliateplusAccount();
            if ($account && $account->getId() && $payment->getId()) {
                try {
                    $refundAmount = $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount();
                    $account->setBalance($account->getBalance() + $refundAmount)
                        ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                        ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                        ->save();
                    $paymentMethod->setBaseRefundAmount($paymentMethod->getBasePaidAmount())
                        ->setRefundAmount($paymentMethod->getPaidAmount())
                        ->save();
                    $payment->setStatus(4)->save();
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * Get Transaction Model
     * @return mixed
     */
    protected function _getTransactionModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction');
    }

    /**
     * @return mixed
     */
    protected function _getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

    /**
     * Get Payment Credit Model
     * @return mixed
     */
    protected function _getPaymentCreditModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Credit');
    }

    /**
     * Get Quote Session in Back-end
     * @return mixed
     */
    protected function _getBackEndQuoteSession(){
        return $this->_objectManager->create('Magento\Backend\Model\Session\Quote');
    }

    /**
     * Get Quote in back-end
     * @return mixed
     */
    protected function _getCheckoutSession(){
        return $this->_helper->getCheckoutSession();
    }

    /**
     * Get Sales Order Model
     * @return mixed
     */
    protected function _getSalesOrderModel(){
        return $this->_objectManager->create('Magento\Sales\Model\Order');
    }

    /**
     * @param $order
     * @return $this
     */
    protected function _createTransactionInBackend($order){
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $now = new \DateTime();
        $orderId = $this->_getBackEndQuoteSession()->getOrder()->getId();
        $currentOrderEdit = $this->_getSalesOrderModel()->load($orderId);
        $customerEmail = $currentOrderEdit->getCustomerEmail();
        $originalIncrementId = $currentOrderEdit->getIncrementId();
        $transactionAffiliate = $this->_getTransactionModel()
            ->getCollection()
            ->addFieldToFilter('order_number', $originalIncrementId)
            ->getFirstItem();
        /* process code in the case :  life time affiliate Edit By Jack */
        $account = '';
        $lifeTimeAff = false;

        $couponCode = $this->_getCheckoutSession()->getData('affiliate_coupon_code');
        $isEnableCouponPlugin = $this->_helper->isModuleEnabled('Magestore_Affiliatepluscoupon');
        $affiliateInfo = $this->_helperCookie->getAffiliateInfo();

        if (!$orderId) {  // when create a new order
            // life time
            $customerOrderId = $order->getCustomerId();
            $accountAndProgramData = $this->_helper->getAccountAndProgramData($customerOrderId);
            $programId = $accountAndProgramData->getProgramId();
            $programName = $accountAndProgramData->getProgramName();
            $lifeTimeAff = $accountAndProgramData->getLifetimeAff();
            $account = $accountAndProgramData->getAccount();
        } else {  // when edit order
            if ((!$couponCode && $transactionAffiliate->getCouponCode()) || !$isEnableCouponPlugin) {
                /* when remove coupon of old order or un-enable coupon plugin */
                $accountAndProgramData = new \Magento\Framework\DataObject(array(
                    'program_id' => '',
                    'program_name' => '',
                    'account' => $account,
                    'lifetime_aff' => $lifeTimeAff,
                ));
                $customerOrderId = $order->getCustomerId();
                $accountAndProgramData = $this->_helper->getAccountAndProgramData($customerOrderId);
                $account = $accountAndProgramData->getAccount();
                if ($account) {  // life time
                    $programId = $accountAndProgramData->getProgramId();
                    $programName = $accountAndProgramData->getProgramName();
                    $lifeTimeAff = $accountAndProgramData->getLifetimeAff();
                } else {  // not life time
                    // get information from old order
                    $accountIdByTransaction = $transactionAffiliate->getAccountId();
                    $account = $this->_accountFactory->create()->load($accountIdByTransaction);
                    $programId = $transactionAffiliate->getProgramId();
                    $programName = $transactionAffiliate->getProgramName();
                    if (!$programId && !$programName) {
                        // if program id = null and program = null =>  get information from session
                        $programData = $this->_getCheckoutSession()->getProgramData();
                        if ($programData) {
                            $programId = $programData->getData('program_id');
                            $programName = $programData->getData('name');
                        }
                    }
                }
            } else {
                $programData = $this->_getCheckoutSession()->getProgramData();
                if (!$couponCode) {
                    if ($programData) {
                        $programId = $programData->getData('program_id');
                        $programName = $programData->getData('name');
                    }
                    $accountIdByTransaction = $transactionAffiliate->getAccountId();
                    $account = $this->_accountFactory->create()
                        ->load($accountIdByTransaction);
                }
            }
        }
        /* end process code */

        $baseDiscount = $order->getBaseAffiliateplusDiscount();
        //$maxCommission = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
        // Before calculate commission
        $commissionObj = new \Magento\Framework\DataObject(array(
            'commission' => 0,
            'default_commission' => true,
            'order_item_ids' => array(),
            'order_item_names' => array(),
            'commission_items' => array(),
            'extra_content' => array(),
            'tier_commissions' => array(),
        ));
        if (!$isEnableCouponPlugin || !$this->_helper->isModuleEnabled('Magestore_Affiliatepluscoupon')) {
            $session = $this->_getCheckoutSession();
            $session->unsAffiliateCouponCode();
        }
        if ($couponCode && $isEnableCouponPlugin) {
            $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
            foreach ($affiliateInfo as $info){
                if ($info['account']) {
                    $account = $info['account'];
                    break;
                }
            }
            if ($account->getUsingCoupon()) {
                $program = $account->getUsingProgram();
                if ($program) {
                    $programId = $program->getId();
                    $programName = $program->getName();
                } else {
                    $programId = 0;
                    $programName = 'Affiliate Program';
                }
            }
        }
        if (!$account){
            return $this;
        }
        // Log affiliate tracking referal - only when has sales
        if ($this->_helperConfig->getCommissionConfig('life_time_sales')) {
            $tracksCollection = $this->_getTransactionModel()
                ->getCollection();
            if ($order->getCustomerId()) {
                $tracksCollection->getSelect()
                    ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?", $order->getCustomerEmail());
            } else {
                $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
            }
            if (!$tracksCollection->getSize()) {
                try {
                    $this->_getTransactionModel()
                        ->setData(
                            array(
                                'account_id' => $account->getId(),
                                'customer_id' => $order->getCustomerId(),
                                'customer_email' => $order->getCustomerEmail(),
                                'created_time' => $now
                            ))
                        ->save();
                } catch (\Exception $e) {
                }
            }
        }
        $this->_eventManager->dispatch('affiliateplus_calculate_commission_before_edit',
            array(
                'order' => $order,
                'program_id' => $programId,
                'commission_obj' => $commissionObj,
                'account' => $account,
            )
        );
        $storeId = $this->_getBackEndQuoteSession()->getStoreId();
        $commissionType = $this->_helperConfig->getCommissionConfig('commission_type', $storeId);
        $commissionValue = floatval($this->_helperConfig->getCommissionConfig('commission', $storeId));
        if (($orderId && $this->_helperCookie->getNumberOrdered() > 1) || (!$orderId && $this->_helperCookie->getNumberOrdered())) {
            if ($this->_helperConfig->getCommissionConfig('use_secondary', $storeId)) {
                $commissionType = $this->_helperConfig->getCommissionConfig('secondary_type', $storeId);
                $commissionValue = floatval($this->_helperConfig->getCommissionConfig('secondary_commission', $storeId));
            }
        }
        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();

        $defaultItemIds = array();
        $defaultItemNames = array();
        $defaultAmount = 0;
        $defCommission = 0;

        // Calculate the total price of items ~~ baseSubtotal
        $baseItemsPrice = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if (in_array($item->getProductId(), $commissionItems)) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {

                foreach ($item->getChildrenItems() as $child) {
                    $baseItemsPrice += $item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                }
            } elseif ($item->getProduct()) {

                $baseItemsPrice += $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
            }
        }
        if ($commissionValue && $commissionObj->getDefaultCommission()) {
            if ($commissionType == 'percentage') {
                if ($commissionValue > 100)
                    $commissionValue = 100;
                if ($commissionValue < 0)
                    $commissionValue = 0;
            }

            foreach ($order->getAllItems() as $item) {
                $affiliateplusCommissionItem = '';
                if ($item->getParentItemId()) {
                    continue;
                }
                if (in_array($item->getProductId(), $commissionItems)) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    // $childHasCommission = false;
                    foreach ($item->getChildrenItems() as $child) {
                        $affiliateplusCommissionItem = '';
                        if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit'){
                            $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                        }else{
                            $baseProfit = $child->getBasePrice();
                        }

                        $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0){
                            continue;
                        }

                        if ($commissionType == "cart_fixed") {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                        } elseif ($commissionType == 'fixed'){
                            $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                        }elseif ($commissionType == 'percentage'){
                            $defaultCommission = $baseProfit * $commissionValue / 100;
                        }
                        $affiliateplusCommissionItem .= $defaultCommission . ",";
                        $commissionObj = new \Magento\Framework\DataObject(
                            [
                            'profit' => $baseProfit,
                            'commission' => $defaultCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                            'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                            ]
                        );
                        $this->_eventManager->dispatch('affiliateplus_calculate_tier_commission', array(
                            'item' => $child,
                            'account' => $account,
                            'commission_obj' => $commissionObj
                        ));

                        if ($commissionObj->getTierCommission()){
                            $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                        }

                        $commission += $commissionObj->getCommission();
                        $child->setAffiliateplusCommission($commissionObj->getCommission());
                        $child->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());
                        $defCommission += $commissionObj->getCommission();
                        $defaultAmount += $child->getBasePrice();
                        $orderItemIds[] = $child->getProductId();
                        $orderItemNames[] = $child->getName();
                        $defaultItemIds[] = $child->getProductId();
                        $defaultItemNames[] = $child->getName();
                    }
                } else {
                    if ($this->_helperConfig->getCommissionConfig('affiliate_type') == 'profit'){
                        $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                    }else{
                        $baseProfit = $item->getBasePrice();
                    }

                    $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                    if ($baseProfit <= 0){
                        continue;
                    }

                    $orderItemIds[] = $item->getProduct() ? $item->getProduct()->getId() : $item->getProductId();
                    $orderItemNames[] = $item->getName();

                    $defaultItemIds[] = $item->getProduct() ? $item->getProduct()->getId() : $item->getProductId();
                    $defaultItemNames[] = $item->getName();

                    if ($commissionType == 'cart_fixed') {
                        $commissionValue = min($commissionValue, $baseItemsPrice);
                        $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                        $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                        $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                    } elseif ($commissionType == 'fixed'){
                        $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                    }elseif ($commissionType == 'percentage'){
                        $defaultCommission = $baseProfit * $commissionValue / 100;
                    }
                    $affiliateplusCommissionItem .= $defaultCommission . ",";
                    $commissionObj = new \Magento\Framework\DataObject(array(
                        'profit' => $baseProfit,
                        'commission' => $defaultCommission,
                        'tier_commission' => array(),
                        'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                        'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                    ));
                    $this->_eventManager->dispatch('affiliateplus_calculate_tier_commission', array(
                        'item' => $item,
                        'account' => $account,
                        'commission_obj' => $commissionObj
                    ));

                    if ($commissionObj->getTierCommission()){
                        $tierCommissions[$item->getProductId()] = $commissionObj->getTierCommission();
                    }
                    $commission += $commissionObj->getCommission();
                    $item->setAffiliateplusCommission($commissionObj->getCommission());
                    $item->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());
                    $defCommission += $commissionObj->getCommission();
                    $defaultAmount += $item->getBasePrice();
                }
            }
        }
        /* if remove coupon, then return Edit By Jack */
        $currentCouponCode = $transactionAffiliate->getCouponCode();
        if (($currentCouponCode && !$this->_getCheckoutSession()->getData('affiliate_coupon_code') && !$baseDiscount) || $account->getStatus() == 2){
            $commission = 0;
        }
        //set Commission Value
        $this->_getBackEndQuoteSession()
            ->setCommission($commission);
        if (!$baseDiscount && !$commission){
            return $this;
        }
        // Create transaction
        $storeId = $this->_getBackEndQuoteSession()
            ->getStore()
            ->getId();
        $transactionData = array(
            'account_id' => $account->getId(),
            'account_name' => $account->getName(),
            'account_email' => $account->getEmail(),
            'customer_id' => $order->getCustomerId(), // $customer->getId(),
            'customer_email' => $order->getCustomerEmail(), // $customer->getEmail(),
            'order_id' => $order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_item_ids' => implode(',', $orderItemIds),
            'order_item_names' => implode(',', $orderItemNames),
            'total_amount' => $order->getBaseSubtotal(),
            'discount' => $baseDiscount,
            'commission' => $commission,
            'created_time' => $now,
            'status' => '2',
            'store_id' => $storeId,
            'extra_content' => $extraContent,
            'tier_commissions' => $tierCommissions,
            'default_item_ids' => $defaultItemIds,
            'default_item_names' => $defaultItemNames,
            'default_commission' => $defCommission,
            'default_amount' => $defaultAmount,
            'type' => 3,
            'program_id' => $programId,
            'program_name' => $programName,
            'coupon_code' => $this->_getCheckoutSession()->getData('affiliate_coupon_code'),
        );
        $transaction = $this->_getTransactionModel()
            ->setData($transactionData)
            ->setId(null);
        $transactionLatest = $this->_getTransactionModel()
            ->getCollection()
            ->getLastItem();

        try {
            if ($transactionLatest->getOrderNumber() != $transactionData['order_number']) {
                $transaction->save();
                if ($transaction->getIsChangedData())
                    $transaction->save();
                if (!$affiliateInfo)
                    $affiliateInfo = '';
                $this->_eventManager->dispatch('affiliateplus_recalculate_commission',
                    array(
                        'transaction' => $transaction,
                        'order' => $order,
                        'affiliate_info' => $affiliateInfo,
                    )
                );
                $this->_eventManager->dispatch('affiliateplus_created_transaction',
                    array(
                        'transaction' => $transaction,
                        'order' => $order,
                    )
                );
                $transaction->sendMailNewTransactionToAccount();
                $transaction->sendMailNewTransactionToSales();
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Save payment if the affiliate use Store Credit to place order
     * @param $order
     */
    protected function _saveStoreCreditPayment($order){
        $baseAmount = $order->getBaseAffiliateCredit();
        if($order->getStatus()== 'pending'){
            if ($baseAmount) {
                $session = $this->_getCheckoutSession();
                $session->setUseAffiliateCredit('');
                $session->setAffiliateCredit(0);
                $now = new \DateTime();
//            $account = $this->_affiliateSession->getAccount();

                $account = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')->load($order->getData('account_ids'));
                $payment = $this->_getPaymentModel()
                    ->setAccountId($account->getId())
                    ->setAccountName($account->getName())
                    ->setAccountEmail($account->getEmail())
                    ->setPaymentMethod('credit')
                    ->setAmount(-$baseAmount)
                    ->setRequestTime($now)
                    ->setStatus(3)
                    ->setIsRequest(1)
                    ->setIsPayerFee(0)
                    ->setData('is_created_by_recurring', 1)
                    ->setData('is_refund_balance', 1);
                if ($this->_helperConfig->getSharingConfig('balance') == 'store') {
                    $payment->setStoreIds($order->getStoreId());
                }
                $paymentMethod = $payment->getPayment();
                $paymentMethod->addData(
                    [
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'base_paid_amount' => -$baseAmount,
                        'paid_amount' => -$order->getAffiliateCredit(),
                    ]
                );
                try {
                    $payment->save();
                    $paymentMethod->savePaymentMethodInfo();

                } catch (\Exception $e) {
                }
            }
        }

    }

    /**
     * Get Tracking Collection
     * @return mixed
     */
    protected function _getTrackingCollection(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Tracking\Collection');
    }

    /**
     * Check if customer is lifetime commission or not and save it
     * @param $order
     */
    protected function _saveLifetimeAccount($order, $account){
        if ($this->_helperConfig->getCommissionConfig('life_time_sales')) {
            $now = new \DateTime();
            $tracksCollection = $this->_getTrackingCollection();
            if ($order->getCustomerId()) {
                $tracksCollection->getSelect()
                    ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?", $order->getCustomerEmail());
            } else {
                $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
            }
            if (!$tracksCollection->getSize()) {
                try {
                    $this->_getTrackingModel()
                        ->setData(array(
                            'account_id' => $account->getId(),
                            'customer_id' => $order->getCustomerId(),
                            'customer_email' => $order->getCustomerEmail(),
                            'created_time' => $now
                        ))
                        ->save();
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * Get base item price to calculate fixed amount per cart
     * @param $order
     * @param $commissionItems
     * @return int
     */
    protected function _getBaseItemsPrice($order, $commissionItems){
        $baseItemsPrice = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            /**
             * Check if the items were calculated in program, it not, they will be calculated in default program
             */
            if (in_array($item->getProductId(), $commissionItems)) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildrenItems() as $child) {
                    $baseItemsPrice += $item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount());
                }
            } elseif ($item->getProduct()) {
                $baseItemsPrice += $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
            }
        }
        return $baseItemsPrice;
    }

    /**
     * Get Tracking Model
     * @return mixed
     */
    protected function _getTrackingModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Tracking');
    }

    /**
     * Calculate commission and create transaction
     * @param $order
     * @return $this
     */
    public function _salesOrderPlaceAfter($order)
    {
        if(!$this->_helper->isAffiliateModuleEnabled() || $this->_helper->isAdmin()){
            return $this;
        }
        /**
         * check to run this function 1 time for 1 order
         */

        if ($order->getId() && $this->_sessionManager->getData("affiliateplus_order_placed_" . $order->getId())) {
            return $this;
        }
        $this->_sessionManager->setData("affiliateplus_order_placed_" . $order->getId(), true);

        /**
         * Use Store Credit to Checkout
         */
        $this->_saveStoreCreditPayment($order);

        if (!$order->getBaseSubtotal()) {
            return $this;
        }

        $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        if ($this->_helper->isAdmin()) {
            $orderId = $this->_backendQuoteSession->getOrder()->getId();
            if($orderId){
                $affiliateInfo = $this->_helper->getAffiliateInfoByOrderId($orderId);
            }
        } else {
            $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        }
        $account = '';
        if (is_array($affiliateInfo) || is_object($affiliateInfo)) {

            foreach ($affiliateInfo as $info) {
                if ($info['account']) {
                    $account = $info['account'];
                    break;
                }
            }
        }
        if($account && $account->getId()){
            /**
             * Log affiliate tracking referal - only when has sales
             */
            $this->_saveLifetimeAccount($order, $account);

            $baseDiscount = $order->getBaseAffiliateplusDiscount();

            /**
             * Before calculate commission
             */
            $commissionObj = new \Magento\Framework\DataObject(array(
                'commission' => 0,
                'default_commission' => true,
                'order_item_ids' => array(),
                'order_item_names' => array(),
                'commission_items' => array(),
                'extra_content' => array(),
                'tier_commissions' => array()
            ));
            $this->_eventManager->dispatch('affiliateplus_calculate_commission_before',
                [
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                    'commission_obj' => $commissionObj,
                ]
            );

            $commissionType = $this->_helperConfig->getCommissionConfig('commission_type');
            $commissionValue = floatval($this->_helperConfig->getCommissionConfig('commission_value'));
            if ($this->_helperCookie->getNumberOrdered()) {
                if ($this->_helperConfig->getCommissionConfig('use_secondary')) {
                    $commissionType = $this->_helperConfig->getCommissionConfig('secondary_type');
                    $commissionValue = floatval($this->_helperConfig->getCommissionConfig('secondary_commission'));
                }
            }

            $commission = $commissionObj->getCommission();
            $orderItemIds = $commissionObj->getOrderItemIds();
            $orderItemNames = $commissionObj->getOrderItemNames();
            $commissionItems = $commissionObj->getCommissionItems();
            $extraContent = $commissionObj->getExtraContent();
            $tierCommissions = $commissionObj->getTierCommissions();

            $defaultItemIds = array();
            $defaultItemNames = array();
            $defaultAmount = 0;
            $defCommission = 0;

            if ($commissionValue >= 0 && $commissionObj->getDefaultCommission() >=0) {
                if ($commissionType == 'percentage') {
                    if ($commissionValue > 100)
                        $commissionValue = 100;
                    if ($commissionValue < 0)
                        $commissionValue = 0;
                }

                $baseItemsPrice = $this->_getBaseItemsPrice($order, $commissionItems);

                foreach ($order->getAllItems() as $item) {
                    $affiliatePlusCommissionItem = '';
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (in_array($item->getProductId(), $commissionItems)) {
                        continue;
                    }

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildrenItems() as $child) {
                            $affiliatePlusCommissionItem = '';
                            if ($this->_helperConfig->getCommissionConfig('affiliate_type') == 'profit'){
                                $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                            }else {
                                $baseProfit = $child->getBasePrice();
                            }
                            $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount();

                            if ($baseProfit <= 0)
                                continue;

                            if ($commissionType == "cart_fixed") {
                                $commissionValue = min($commissionValue, $baseItemsPrice);
                                $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount();
                                $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                                $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                            } elseif ($commissionType == 'fixed'){
                                $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            } elseif ($commissionType == 'percentage'){
                                $defaultCommission = $baseProfit * $commissionValue / 100;
                            }

                            /**
                             * Changed By Adam 14/08/2014: Invoice tung phan
                             */
                            $affiliatePlusCommissionItem .= $defaultCommission . ",";
                            $commissionObj = new \Magento\Framework\DataObject(array(
                                'profit' => $baseProfit,
                                'commission' => $defaultCommission,
                                'tier_commission' => array(),
                                'base_item_price' => $baseItemsPrice,
                                'affiliateplus_commission_item' => $affiliatePlusCommissionItem
                            ));
                            $this->_eventManager->dispatch('affiliateplus_calculate_tier_commission',
                                [
                                    'item' => $child,
                                    'account' => $account,
                                    'commission_obj' => $commissionObj
                                ]
                            );

                            if ($commissionObj->getTierCommission())
                                $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                            $commission += $commissionObj->getCommission();
                            $child->setAffiliateplusCommission($commissionObj->getCommission());

                            /**
                             * Changed By Adam 14/08/2014: Invoice tung phan
                             */
                            $child->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());

                            $defCommission += $commissionObj->getCommission();
                            $defaultAmount += $child->getBasePrice();

                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();

                            $defaultItemIds[] = $child->getProduct()->getId();
                            $defaultItemNames[] = $child->getName();
                        }
                    } else {
                        if ($this->_helperConfig->getCommissionConfig('affiliate_type') == 'profit'){
                            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                        }else{
                            $baseProfit = $item->getBasePrice();
                        }

                        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
                        if ($baseProfit <= 0)
                            continue;

                        if ($item->getProduct()){
                            $inProductId = $item->getProduct()->getId();
                        }else{
                            $inProductId = $item->getProductId();
                        }

                        $orderItemIds[] = $inProductId;
                        $orderItemNames[] = $item->getName();

                        $defaultItemIds[] = $inProductId;
                        $defaultItemNames[] = $item->getName();

                        if ($commissionType == 'cart_fixed') {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                        } elseif ($commissionType == 'fixed'){
                            $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        }elseif ($commissionType == 'percentage'){
                            $defaultCommission = $baseProfit * $commissionValue / 100;
                        }

                        /**
                         * Changed By Adam 14/08/2014: Invoice tung phan
                         */
                        $affiliatePlusCommissionItem .= $defaultCommission . ",";
                        $commissionObj = new \Magento\Framework\DataObject(array(
                            'profit' => $baseProfit,
                            'commission' => $defaultCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice,
                            'affiliateplus_commission_item' => $affiliatePlusCommissionItem
                        ));
                        $this->_eventManager->dispatch('affiliateplus_calculate_tier_commission',
                            [
                                'item' => $item,
                                'account' => $account,
                                'commission_obj' => $commissionObj
                            ]
                        );

                        if ($commissionObj->getTierCommission()){
                            $tierCommissions[$item->getProductId()] = $commissionObj->getTierCommission();
                        }

                        $commission += $commissionObj->getCommission();

                        $item->setAffiliateplusCommission($commissionObj->getCommission());

                        /**
                         * Changed By Adam 14/08/2014: Invoice tung phan
                         */
                        $item->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());

                        $defCommission += $commissionObj->getCommission();
                        $defaultAmount += $item->getBasePrice();
                    }
                }
            }

            if (!$baseDiscount && !$commission){
                return $this;
            }
            try{
                $this->_createTransaction($account,
                    $order,
                    $orderItemIds,
                    $orderItemNames,
                    $baseDiscount,
                    $commission,
                    $extraContent,
                    $tierCommissions,
                    $defaultItemIds,
                    $defaultItemNames,
                    $defCommission,
                    $defaultAmount,
                    $affiliateInfo
                );
            } catch(\Exception $e) {
            }
        }
    }

    /**
     * Create Transaction
     * @param $account
     * @param $order
     * @param $orderItemIds
     * @param $orderItemNames
     * @param $baseDiscount
     * @param $commission
     * @param $extraContent
     * @param $tierCommissions
     * @param $defaultItemIds
     * @param $defaultItemNames
     * @param $defCommission
     * @param $defaultAmount
     */
    protected function _createTransaction(
        $account,
        $order,
        $orderItemIds,
        $orderItemNames,
        $baseDiscount,
        $commission,
        $extraContent,
        $tierCommissions,
        $defaultItemIds,
        $defaultItemNames,
        $defCommission,
        $defaultAmount,
        $affiliateInfo
    ){
        $now = new \DateTime();
        /**
         * Prepare transaction data
         */
        $transactionData = array(
            'account_id' => $account->getId(),
            'account_name' => $account->getName(),
            'account_email' => $account->getEmail(),
            'customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getCustomerEmail(),
            'order_id' => $order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_item_ids' => implode(',', $orderItemIds),
            'order_item_names' => implode(',', $orderItemNames),
            'total_amount' => $order->getBaseSubtotal(),
            'discount' => $baseDiscount,
            'commission' => $commission,
            'created_time' => $now,
            'status' => Transaction::TRANSACTION_PENDING,
            'store_id' => $this->_storeManager->getStore()->getId(),
            'extra_content' => $extraContent,
            'tier_commissions' => $tierCommissions,
            'default_item_ids' => $defaultItemIds,
            'default_item_names' => $defaultItemNames,
            'default_commission' => $defCommission,
            'default_amount' => $defaultAmount,
            'type' => Transaction::TRANSACTION_TYPE_SALES,
        );

        if ($account->getUsingCoupon()) {
            $session = $this->_getCheckoutSession();
            $transactionData['coupon_code'] = $session->getData('affiliate_coupon_code');
            if ($program = $account->getUsingProgram()) {
                $transactionData['program_id'] = $program->getId();
                $transactionData['program_name'] = $program->getName();
            } else {
                $transactionData['program_id'] = 0;
                $transactionData['program_name'] = 'Affiliate Program';
            }
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
        }else {
            if (!$this->_helper->isModuleEnabled('Magestore_Affiliateplusprogram')) {
                $transactionData['program_id'] = 0;
                $transactionData['program_name'] = 'Affiliate Program';
            }
        }

        $transaction = $this->_getTransactionModel()
            ->setData($transactionData)
            ->setId(null);

        $this->_eventManager->dispatch('affiliateplus_calculate_commission_after',
            [
                'transaction' => $transaction,
                'order' => $order,
                'affiliate_info' => $affiliateInfo,
            ]
        );

        try {
            $transaction->save();
            $this->_eventManager->dispatch('affiliateplus_recalculate_commission',
                [
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ]
            );

            if ($transaction->getIsChangedData())
                $transaction->save();
            $this->_eventManager->dispatch('affiliateplus_created_transaction',
                [
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ]
            );

            $transaction->sendMailNewTransactionToAccount();
            $transaction->sendMailNewTransactionToSales();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $order
     * @return bool
     */
    protected function _isExistedTransactionByOrder($order){
        $collection = $this->_getTransactionModel()->getCollection()
            ->addFieldToFilter('order_id', $order->getId())
            ;
        if($collection->getSize()){
            return true;
        }
        return false;
    }
}
