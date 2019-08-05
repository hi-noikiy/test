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
namespace Magestore\Affiliateplus\Model;
use Magento\Framework\App\Area;
/**
 * Model Account
 */
class Transaction extends AbtractModel
{
    /**
     * Transaction complete status
     */
    const TRANSACTION_COMPLETE = '1';

    /**
     * Transaction pending status
     */
    const TRANSACTION_PENDING = '2';

    /**
     * Transaction canceled status
     */
    const TRANSACTION_CANCELED = '3';

    /**
     * Transaction onhold status
     */
    const TRANSACTION_ONHOLD = '4';

    /**
     * Transaction type: Sales
     */
    const TRANSACTION_TYPE_SALES = '3';

    /**
     * General email identify path
     */
    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';

    /**
     * Sales email identify path
     */
    const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';

    /**
     * New transaction email template that will be sent to affiliate
     */
    const XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/new_transaction_account_email_template';

    /**
     * New transaction email template that will be sent to administrator
     */
    const XML_PATH_NEW_TRANSACTION_SALES_EMAIL = 'affiliateplus/email/new_transaction_sales_email_template';

    /**
     * Update transaction email template when the transaction's is changed
     */
    const XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/updated_transaction_account_email_template';

    /**
     * Reduce commission email template that will be sent to affiliate
     */
    const XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL = 'affiliateplus/email/reduce_commission_account_email_template';

    /**
     * Refund email template that will be sent to affiliate when the order is refunded
     */
    const XML_PATH_SENT_MAIL_REFUND = 'affiliateplus/email/sent_mail_refund_email_template';

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Transaction');
    }

    /**
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $storeViewId
     * @return $this
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;
        return $this;
    }

    /**
     * Get available statuses.
     *
     * @return void
     */
    public static function getTransactionStatus()
    {
        return [
            self::TRANSACTION_COMPLETE => __('Complete'),
            self::TRANSACTION_PENDING => __('Pending'),
            self::TRANSACTION_CANCELED => __('Canceled'),
            self::TRANSACTION_ONHOLD => __('On Hold'),
        ];
    }

    /**
     * Get Affiliate Account Model
     * @return \Magestore\Affiliateplus\Model\Account
     */
    protected function _getAffiliateAccountModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
    }

    /**
     * Get Customer Model
     * @return \Magento\Customer\Model\Customer
     */
    protected function _getCustomerModel(){
        return $this->_objectManager->create('Magento\Customer\Model\Customer');
    }

    /**
     * Get Sales Order Model
     * @return \Magento\Sales\Model\Order
     */
    protected function _getSalesOrderModel(){
        return $this->_objectManager->create('Magento\Sales\Model\Order');
    }

    /**
     * Get Config Helper
     *
     * @return Magestore\Affiliateplus\Helper\Config
     */
    protected function _getConfigHelper() {
        return $this->_helperConfig;
    }

    /**
     * Get Sales Order Invoice Item Collection
     * @return Magento\Sales\Model\ResourceModel\Order\Invoice\Item\Collection
     */
    protected function _getSalesOrderInvoiceItemCollection(){
        return $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Invoice\Item\Collection');
    }

    /**
     * @return mixed
     */
    public function canRestore() {
        return $this->getData('transaction_is_deleted');
    }


    /**
     * Complete Transaction
     * @return $this
     */
    public function complete() {

        if ($this->canRestore()) {
            return $this;
        }
        if (!$this->getId()) {
            return $this;
        }

        if($this->getStatus() == self::TRANSACTION_CANCELED){
            return $this;
        }
        $storeId = $this->getStoreId();

        // get affiliate account from transaction (account_id)
        $account = $this->_getAffiliateAccountModel()
            ->setStoreId($storeId)
            ->load($this->getAccountId());

        /*Add extra commission to affiliate: Commission By Level plugin*/
        if ($this->getStatus() != self::TRANSACTION_COMPLETE) {
            $additionalCommission = $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
            try {
                if ($additionalCommission)
                    $account->setBalance($account->getData('balance') + $additionalCommission)->save();
            } catch (\Exception $e) {

            }
        }

        // Get Order from transaction (order_id)
        $order = $this->_getSalesOrderModel()
            ->load($this->getOrderId());

        try {
            $commission = 0;
            $transactionCommission = 0;
            $transactionDiscount = 0;
            $status = $this->getStatus();
            $configOrderStatus = $this->_getConfigHelper()->getCommissionConfig('updatebalance_orderstatus', $storeId);
            $configOrderStatus = $configOrderStatus ? $configOrderStatus : \Magento\Sales\Model\Order::STATE_PROCESSING;

            if ($configOrderStatus == \Magento\Sales\Model\Order::STATE_COMPLETE) {
                // Check if transaction is not completed
                if ($this->getStatus() != self::TRANSACTION_COMPLETE) {
                    foreach ($order->getAllItems() as $item) {
                        if ($item->getAffiliateplusCommission()) {
                            $affiliateplusCommissionItem = explode(",", $item->getAffiliateplusCommissionItem());

                            $totalComs = array_sum($affiliateplusCommissionItem);
                            $totalComs = $totalComs ? $totalComs : $item->getAffiliateplusCommission();
                            $firstComs = $affiliateplusCommissionItem[0];
                            if($firstComs) {
                                $commission += $firstComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                            } else {
                                $commission += $item->getAffiliateplusCommission();
                            }
                            $transactionCommission += $totalComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                        }
                        //update tier commission to tier affiliate when partial invoice
                        $this->_eventManager->dispatch('update_tiercommission_to_tieraffiliate_partial_invoice', array('transaction' => $this, 'item' => $item, 'invoice_item' => ''));
                    }
                }
            } else {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getAffiliateplusCommission()) {
                        $collection = $this->_getSalesOrderInvoiceItemCollection();
                        $collection->getSelect()
                            ->where('affiliateplus_commission_flag = 0')
                            ->where('order_item_id = ' . $item->getId())
                        ;

                        $affiliateplusCommissionItem = explode(",", $item->getAffiliateplusCommissionItem());

                        $totalComs = array_sum($affiliateplusCommissionItem);
                        $totalComs = $totalComs ? $totalComs : $item->getAffiliateplusCommission();
                        $firstComs = $affiliateplusCommissionItem[0];

                        if ($collection->getSize()) {
                            foreach ($collection as $invoiceItem) {
                                if ($invoiceItem && $invoiceItem->getId()) {
                                    if($firstComs)
                                        $commission += $firstComs * $invoiceItem->getQty() / $item->getQtyOrdered();
                                    else
                                        $commission += $item->getAffiliateplusCommission();

                                    $invoiceItem->setAffiliateplusCommissionFlag(1)->save();

                                    //update tier commission to tier affiliate when partial invoice
                                    $this->_eventManager->dispatch('update_tiercommission_to_tieraffiliate_partial_invoice', array('transaction' => $this, 'item' => $item, 'invoice_item' => $invoiceItem));
                                }
                            }
                        }
                        // check if doesn't subtract commission from affiliate account balance when credit memo is created
                        if (!$this->_getConfigHelper()->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
                            $transactionCommission += $totalComs * ($item->getQtyInvoiced()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced()) / $item->getQtyOrdered();
                        } else {
                            $transactionCommission += $totalComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                        }
                    }
                }
            }

            if ($commission) {
                $status = self::TRANSACTION_COMPLETE;
                $account->setBalance($account->getData('balance') + $commission)
                    ->save();
                if ($transactionCommission) {
                    $this->setCommission($transactionCommission);
                }
                if ($transactionDiscount) {
                    if ($transactionDiscount <= 0)
                        $this->setDiscount(0);
                    else
                        $this->setDiscount(-$transactionDiscount);
                }
                $this->setStatus($status)->save();

                if ($transactionCommission) {

                    //update tiercommission to affiliatepluslevel_transaction table
                    $this->_eventManager->dispatch('update_tiercommission_to_transaction_partial_invoice', array('transaction' => $this, 'order' => $order));

                    //Update commission to affiliateplusprogram_transaction table
                    $this->_eventManager->dispatch('update_commission_to_affiliateplusprogram_transaction_partial_invoice', array('transaction' => $this, 'order' => $order));
                }
                $this->sendMailUpdatedTransactionToAccount(true);
            }
        } catch (\Exception $e) {

        }
        return $this;
    }

    /**
     * Holding transaction
     * @return $this
     */
    public function hold() {
        if ($this->canRestore()){
            return $this;
        }
        if (!$this->getId()){
            return $this;
        }
        if ($this->getStatus() != self::TRANSACTION_PENDING){
            return $this;
        }
        try {
            $now = new \DateTime();
            $this->setStatus(self::TRANSACTION_ONHOLD)
                ->setHoldingFrom($now)
                ->save();
        } catch (\Exception $e) {

        }
        return $this;
    }

    /**
     * Unhold the holding transaction
     * @return $this
     */
    public function unHold() {
        if ($this->canRestore()){
            return $this;
        }
        if (!$this->getId()){
            return $this;
        }
        if ($this->getStatus() != self::TRANSACTION_ONHOLD){
            return $this;
        }
        try{
            $this->setStatus(self::TRANSACTION_PENDING)->complete();
        } catch(\Exception $e){

        }
        return $this;
    }

    /**
     * Reduce commission from affiliate's balance when the customer refund order
     * @param $creditMemo
     * @return $this
     */
    public function reduce($creditMemo) {
        //Just reduce the commission of complete transaction
        if ($this->getStatus() == self::TRANSACTION_COMPLETE) {
            if ($this->canRestore()) {
                return $this;
            }
            if (!$this->getId() || !$creditMemo->getId()) {
                return $this;
            }

            $reducedIds = explode(',', $this->getCreditmemoIds());
            if (is_array($reducedIds) && in_array($creditMemo->getId(), $reducedIds)) {
                return $this;
            }
            $reducedIds[] = $creditMemo->getId();
            // calculate reduced commission
            // Reduce commission for affiliate level 0
            $reduceCommission = 0;
            // Reduce commission for transaction (all affiliate + tier affiliate)
            $reduceTransactionCommission = 0;
            $reduceTransactionDiscount = 0;
            foreach ($creditMemo->getAllItems() as $item) {
                if ($item->getOrderItem()->isDummy()) {
                    continue;
                }

                // Calculate the reduce commission for affiliate
                if (!$item->getAffiliateplusCommissionFlag()) {
                    $orderItem = $item->getOrderItem();
                    if ($orderItem->getAffiliateplusCommission()) {
                        // Calculate the reduce commission for affiliate
                        $affiliateplusCommissionItem = explode(",", $orderItem->getAffiliateplusCommissionItem());
                        $firstComs = $affiliateplusCommissionItem[0];
                        $reduceCommission += $firstComs * $item->getQty() / $orderItem->getQtyOrdered();

                        // Calculate the reduce commission for transaction
                        $orderItemQty = $orderItem->getQtyOrdered();
                        $orderItemCommission = (float) $orderItem->getAffiliateplusCommission();
                        if ($orderItemCommission && $orderItemQty) {
                            $reduceTransactionCommission += $orderItemCommission * $item->getQty() / $orderItemQty;
                        }

                        $reduceTransactionDiscount += $orderItem->getBaseAffiliateplusAmount() * $item->getQty() / $orderItemQty;

                        $item->setAffiliateplusCommissionFlag(1)->save();

                        $this->_eventManager->dispatch('update_tiercommission_to_tieraffiliate_partial_refund', array(
                            'transaction' => $this,
                            'creditmemo_item' => $item,
                        ));
                    }
                }
            }

            if ($reduceCommission <= 0) {
                return $this;
            }
            // check reduced commission is over than total commission
            if ($reduceTransactionCommission > $this->getCommission()) {
                $reduceTransactionCommission = $this->getCommission();
            }

            $account = $this->_getAffiliateAccountModel()
                ->setStoreId($this->getStoreId())
                ->load($this->getAccountId());
            try {
                $commission = $reduceCommission + $this->getCommissionPlus() * $reduceTransactionCommission / $this->getCommission() + $reduceTransactionCommission * $this->getPercentPlus() / 100;

                if ($commission) {
                    $account->setBalance($account->getData('balance') - $commission)
                        ->save();
                }

                $creditMemoIds = implode(',', array_filter($reducedIds));
                $this->setCreditmemoIds($creditMemoIds);
                $this->setCommissionPlus($this->getCommissionPlus() - $this->getCommissionPlus() * $reduceTransactionCommission / $this->getCommission());
                $order = $creditMemo->getOrder();
                if ($reduceTransactionCommission) {
                    if ($this->getCommission() <= $reduceTransactionCommission && $order->getBaseSubtotal() == $order->getBaseSubtotalRefunded()) {
                        $this->setCommission(0)
                            ->setStatus(3);
                    } else {
                        $this->setCommission($this->getCommission() - $reduceTransactionCommission);
                    }
                }

                if ($reduceTransactionDiscount) {
                    if ($this->getDiscount() > $reduceTransactionDiscount){
                        $this->setDiscount(0);
                    }else{
                        $this->setDiscount($this->getDiscount() + $reduceTransactionDiscount);
                    }
                }

                $this->save();

                if ($reduceTransactionCommission) {
                    // Update affiliateplusprogram transaction
                    $this->_eventManager->dispatch('update_affiliateplusprogram_transaction_partial_refund', array(
                        'transaction' => $this,
                        'creditmemo' => $creditMemo,
                    ));

                    // update balance for tier transaction
                    $commissionObj = new \Magento\Framework\DataObject(array(
                        'base_reduce' => $reduceTransactionCommission,
                        'total_reduce' => $commission
                    ));

                    $this->_eventManager->dispatch('affiliateplus_reduce_transaction', array(
                        'transaction' => $this,
                        'creditmemo' => $creditMemo,
                        'commission_obj' => $commissionObj
                    ));
                    // Total commission that will be subtracted from this transaction
                    $reduceCommission = $commissionObj->getBaseReduce();

                    // Total commission that will be subtracted from affiliate's balance
                    $commission = $commissionObj->getTotalReduce();

                    // Send email for affiliate account
                    $this->sendMailReduceCommissionToAccount($reduceCommission, $commission);
                }
            } catch (\Exception $e) {
            }
        }
        return $this;
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
    protected function _getBackEndQuoteModel(){
        return $this->_getBackEndQuoteSession()
            ->getQuote();
    }

    protected function _getHelperCookie(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Helper\Cookie');
    }


    /**
     * Cancel transaction when the order's status is matched the configuration in backend
     * @return $this
     */
    public function cancel() {
        if ($this->canRestore()) {
            return $this;
        }
        if (!$this->getId()) {
            return $this;
        }
        $storeId = $this->getStoreId();

        if ($this->getStatus() == self::TRANSACTION_PENDING || $this->getStatus() == self::TRANSACTION_ONHOLD) {
            try {
                $this->setCommission(0);
                $this->setDiscount(0);
                $status = $this->getStatus();

                $this->setStatus(self::TRANSACTION_CANCELED)
                    ->save();

                $this->_eventManager->dispatch('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                //update affiliateplusprogram_transaction
                $this->_eventManager->dispatch('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

                $this->sendMailUpdatedTransactionToAccount(false);
            } catch (\Exception $e) {

            }
        } elseif ($this->getStatus() == self::TRANSACTION_COMPLETE) {
            $status = $this->getStatus();
            // Remove commission for affiliate account
            $account = $this->_getAffiliateAccountModel()
                ->setStoreId($this->getStoreId())
                ->load($this->getAccountId());
            try {
                $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
                //Jack update 27/07
                $orderId = $this->_getBackEndQuoteSession()->getOrder()->getId();
                $affiliateInfo = $this->_getHelperCookie()->getAffiliateInfo();
                $affiliateAccount = '';
                foreach ($affiliateInfo as $info){
                    if ($info['account']) {
                        $affiliateAccount = $info['account'];
                        break;
                    }
                }
                /* update balance after edit Order by Jack  */
                if ($orderId) {
                    $orderNumber = $this->_getSalesOrderModel()->load($orderId)->getIncrementId();
                    $transactionInfo = $this->getCollection()
                        ->addFieldToFilter('order_number', $orderNumber)->getFirstItem();
                    if (!$affiliateAccount) {
                        $affiliateAccount = $this->_getAffiliateAccountModel()
                            ->setStoreId($this->getStoreId())
                            ->load($this->getAccountId());
                    }
                    if ($transactionInfo->getStatus() == self::TRANSACTION_COMPLETE) {
                        $newCommission = $this->_getBackEndQuoteSession()->getCommission();
                        if (($affiliateAccount->getId() && ($affiliateAccount->getId() == $transactionInfo->getAccountId())) || !$affiliateAccount->getId()) {
                            $affiliateAccount->setBalance($affiliateAccount->getBalance() + ($newCommission - ($transactionInfo->getCommission())))->save();
                        } else if ($affiliateAccount->getId() && ($affiliateAccount->getId() != $transactionInfo->getAccountId())) {
                            $lastAffiliateAccount = $this->_getAffiliateAccountModel()->load($transactionInfo->getAccountId());
                            $lastAffiliateAccount->setBalance($lastAffiliateAccount->getBalance() - ($transactionInfo->getCommission()))->save();
                            $affiliateAccount->setBalance($affiliateAccount->getBalance() + $newCommission)->save();
                        }
                        //unset session
                        $this->_getBackEndQuoteSession()->unsCommission();
                    }
                    $this->setCommission(0)
                        ->setDiscount(0)
                        ->setStatus(self::TRANSACTION_CANCELED)
                        ->save();

                    //update balance tier affiliate in affiliatepluslevel_transaction
                    $this->_eventManager->dispatch('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                    //update affiliateplusprogram_transaction
                    $this->_eventManager->dispatch('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

                    // Send email to affiliate account
                    $this->sendMailUpdatedTransactionToAccount(false);
                }
                /* end update balance  */
                else {
                    if (!$this->_helperConfig->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
                        $account->setBalance($account->getData('balance') - $commission)
                        ->save();

                        //update balance tier affiliate in affiliatepluslevel_transaction
                        $this->_eventManager->dispatch('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                        //update affiliateplusprogram_transaction
                        $this->_eventManager->dispatch('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

                        $this->setCommission(0)
                            ->setDiscount(0)
                            ->setStatus(self::TRANSACTION_CANCELED)
                            ->save();

                        // Send email to affiliate account
                        $this->sendMailUpdatedTransactionToAccount(false);
                    }

                }
            } catch (\Exception $e) {

            }
        }
        return $this;
    }

    /**
     * Cancel Transaction when the order is canceled
     * @return $this
     * @throws \Exception
     */
    public function cancelTransaction() {
        if ($this->canRestore()){
            return $this;
        }
        if (!$this->getId()){
            return $this;
        }
        if ($this->getStatus() == self::TRANSACTION_COMPLETE) {
            // Remove commission for affiliate account
            $account = $this->_getAffiliateAccountModel()
                ->setStoreId($this->getStoreId())
                ->load($this->getAccountId());
            $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
            if ($account->getBalance() < $commission) {
                throw new \Exception(__('Account not enough balance to cancel'));
            }
            try{
                $account->setBalance($account->getBalance() - $commission)
                    ->save();
            }catch(\Exception $e){

            }
        }

        $status = $this->getStatus();

        //update balance tier affiliate in affiliatepluslevel_transaction
        $this->_eventManager->dispatch('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

        //update affiliateplusprogram_transaction
        $this->_eventManager->dispatch('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

        try{
            $this->setCommission(0)
                ->setDiscount(0)
                ->setStatus(self::TRANSACTION_CANCELED)
                ->save();
        } catch(\Exception $e){

        }
        return $this;
    }

    /**
     * Send email to affiliate when a new transaction is created
     * @return $this
     */
    public function sendMailNewTransactionToAccount() {
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_account_new_transaction')){
            return $this;
        }

        $store = $this->_storeManager->getStore($this->getStoreId());

        $account = $this->_getAffiliateAccountModel()->load($this->getAccountId());

        if (!$account->getNotification()){
            return $this;
        }

        //update commission tier affiliate
        $this->_eventManager->dispatch('affiliateplus_reset_transaction_commission', array('transaction' => $this));

        $this->setProducts($this->_helper->getFrontendProductHtmls($this->getOrderItemIds()))
            ->setTotalAmountFormated($this->_helper->convertCurrency($this->getTotalAmount()))
            ->setCommissionFormated($this->_helper->convertCurrency($this->getCommission()))
            ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
            ->setPlusCommissionFormated($this->_helper->convertCurrency($this->getPlusCommission()))
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setCreatedAtFormated($this->_helper->formatDate($this->getCreatedTime(), \IntlDateFormatter::MEDIUM))
        ;

        $template = $this->_helper->getConfig(self::XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();
        $this->setTransEmailIdentSupport($this->_helper->getConfig('trans_email/ident_support/email'));


        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(
                [
                    'transaction' => $this,
                    'store' => $store,
                    'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                    'sender_name' => $sender['name']
                ]
            )
            ->setFrom($sender)
            ->addTo($account->getEmail(), $account->getName())
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email to administrator (Sales) when a new transaction is created
     * @return $this
     */
    public function sendMailNewTransactionToSales() {
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_sales_new_transaction')){
            return $this;
        }

        $store = $this->_storeManager->getStore($this->getStoreId());
        $sales = $this->_helper->getConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId());
        $account = $this->_getAffiliateAccountModel()->load($this->getAccountId());
        $customer = $this->_getCustomerModel()->load($this->getCustomerId());

        $this->setCustomerName($this->getCustomerName())
            ->setCustomerEmail($this->getCustomerEmail())
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setProducts($this->_helper->getBackendProductHtml($this->getOrderItemIds()))
            ->setTotalAmountFormated($this->_helper->convertCurrency($this->getTotalAmount()))
            ->setCommissionFormated($this->_helper->convertCurrency($this->getCommission()))
            ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
            ->setPlusCommissionFormated($this->_helper->convertCurrency($this->getPlusCommission()))
            ->setDiscountFormated($this->_helper->convertCurrency($this->getDiscount()))
            ->setCreatedAtFormated($this->_helper->formatDate($this->getCreatedTime(), \IntlDateFormatter::MEDIUM))
            ->setSalesName($sales['name']);

        $template = $this->_helper->getConfig(self::XML_PATH_NEW_TRANSACTION_SALES_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(
                [
                    'transaction' => $this,
                    'store' => $store,
                    'sender_name' => $sender['name']
                ]
            )
            ->setFrom($sender)
            ->addTo($sales['email'], $sales['name'])
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email to affiliate when his transaction is updated
     * @param $isCompleted
     * @return $this
     */
    public function sendMailUpdatedTransactionToAccount($isCompleted) {
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_account_updated_transaction')){
            return $this;
        }

        $store = $this->_storeManager->getStore($this->getStoreId());
        $account = $this->_getAffiliateAccountModel()->load($this->getAccountId());

        if (!$account->getNotification()){
            return $this;
        }
        //update commission tier affiliate
        $this->_eventManager->dispatch('affiliateplus_reset_transaction_commission', array('transaction' => $this));

        $this->setProducts($this->_helper->getFrontendProductHtmls($this->getOrderItemIds()))
            ->setTotalAmountFormated($this->_helper->convertCurrency($this->getTotalAmount()))
            ->setCommissionFormated($this->_helper->convertCurrency($this->getCommission()))
            ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
            ->setPlusCommissionFormated($this->_helper->convertCurrency($this->getPlusCommission()))
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setCreatedAtFormated($this->_helper->formatDate($this->getCreatedTime(), \IntlDateFormatter::MEDIUM))
            ->setIsCompleted($isCompleted);

        $template = $this->_helper->getConfig(self::XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(
                [
                    'transaction' => $this,
                    'store' => $store,
                    'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                    'sender_name' => $sender['name']
                ]
            )
            ->setFrom($sender)
            ->addTo($account->getEmail(), $account->getName())
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email to affiliate when the commission is reduce
     * @param $reduceCommission
     * @param $totalReduce
     * @return $this
     */
    public function sendMailReduceCommissionToAccount($reduceCommission, $totalReduce) {
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_account_updated_transaction')){
            return $this;
        }

        $store = $this->_storeManager->getStore($this->getStoreId());
        $account = $this->_getAffiliateAccountModel()->load($this->getAccountId());
        if (!$account->getNotification()){
            return $this;
        }

        $this->_eventManager->dispatch('affiliateplus_reset_transaction_commission', array('transaction' => $this));
        $this->setProducts($this->_helper->getFrontendProductHtmls($this->getOrderItemIds()))
            ->setTotalAmountFormated($this->_helper->convertCurrency($this->getTotalAmount()))
            ->setCommissionFormated($this->_helper->convertCurrency($this->getCommission()))
            ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
            ->setPlusCommissionFormated($this->_helper->convertCurrency($this->getPlusCommission()))
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setCreatedAtFormated($this->_helper->formatDate($this->getCreatedTime(), \IntlDateFormatter::MEDIUM))
            ->setReducedCommission($this->_helper->convertCurrency($reduceCommission))
            ->setTotalReduced($this->_helper->convertCurrency($totalReduce))
            ->setAffiliateTransactionUrl($this->_urlBuilder->getUrl('affiliateplus/index/listTransaction'))
        ;
        $template = $this->_helper->getConfig(self::XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(
                [
                    'transaction' => $this,
                    'store' => $store,
                    'trans_email_ident_support' => $this->_helper->getConfig('trans_email/ident_support/email'),
                    'store_phone_information' => $this->_helper->getConfig('general/store_information/phone'),
                    'sender_name' => $sender['name']
                ]
            )
            ->setFrom($sender)
            ->addTo($account->getEmail(), $account->getName())
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }
}
