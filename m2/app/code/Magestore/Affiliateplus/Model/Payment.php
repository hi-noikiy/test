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

/**
 * Model banner
 */
class Payment extends AbtractModel
{
    const PAYMENT_PENDING = '1';

    const PAYMENT_PROCESSING = '2';

    const PAYMENT_COMPLETE = '3';

    const PAYMENT_CANCELED = '4';

    const PAYMENT_METHOD_PAYPAL = 'paypal';

    const PAYMENT_METHOD_MONEY_BOOKER = 'moneybooker';

    const PAYMENT_METHOD_BANK = 'bank';

    const PAYMENT_METHOD_OFFILE = 'offile';

    const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';

    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';

    const XML_PATH_REQUEST_PAYMENT_EMAIL = 'affiliateplus/email/request_payment_email_template';

    const XML_PATH_PROCESS_PAYMENT_EMAIL = 'affiliateplus/email/process_payment_email_template';

    protected $_eventPrefix = 'affiliateplus_payment';

    protected $_eventObject = 'affiliateplus_payment';

    protected $_affiliateplus_account = '';
    protected $_payment = '';

    /**
     * @var
     */
    protected $_storeViewId;

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment');
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
    public static function getPaymentStatus()
    {
        return [
                self::PAYMENT_PENDING => __('Pending'),
                self::PAYMENT_PROCESSING => __('Processing'),
                self::PAYMENT_COMPLETE => __('Complete'),
                self::PAYMENT_CANCELED => __('Canceled')
        ];
    }

    /**
     * @return mixed|string
     */
    public function getPayment() {
        if (!$this->_payment) {
            $this->_eventManager->dispatch($this->_eventPrefix . '_get_payment_before', $this->_getEventData());

            $paymentMethodCode = $this->getPaymentMethod();
            $storeId = $this->getStoreViewId();
            try {
                $paymentMethod = $this->_helperPayment->getPaymentMethod($paymentMethodCode, $storeId);

                $paymentMethod->setPayment($this);

                $paymentMethod->loadPaymentMethodInfo();

                $this->_payment = $paymentMethod;
                $payment_method = ['payment' => $paymentMethod];
                $params = array_merge($this->_getEventData(), $payment_method);
                $this->_eventManager->dispatch($this->_eventPrefix . '_get_payment_after', $params);
            } catch (\Exception $e) {

            }
        }

        return $this->_payment;
    }

    /**
     * @return $this
     */
    public function addPaymentInfo() {
        if (!$this->hasData('add_payment_info')) {
            $this->_eventManager->dispatch($this->_eventPrefix . '_add_paymentinfo_before', $this->_getEventData());
            $paymentMethod = $this->getPayment();
            if ($paymentMethod) {
                foreach ($paymentMethod->getData() as $key => $value){
                    $this->setData($paymentMethod->getPaymentCode() . '_' . $key, $value);
                }
                $this->setData('payment_method_label', $paymentMethod->getLabel());
                $this->setData('payment_method_info', $paymentMethod->getInfoString());
                $this->setData('payment_method_html', $paymentMethod->getInfoHtml());
                $this->setData('payment_fee', $paymentMethod->calculateFee());
                $this->setData('add_payment_info', true);
            }

            $this->_eventManager->dispatch($this->_eventPrefix . '_add_paymentinfo_after', $this->_getEventData());
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function beforeSave() {
        $this->addPaymentInfo();
        if ($this->getData('fee') == NULL) {
            $this->setData('fee', $this->getData('payment_fee'));
        }
        if (!$this->getData('store_ids')) {
            $this->setData('store_ids', implode(',', array_keys($this->_storeManager->getStores())));
        }
        // Apply tax when create Payment
        if (!$this->getId() && $this->getPaymentMethod() != 'credit') {
            $this->applyTax();
        }

        // send email for completed payment
        if ($this->getOrigData('status') < 3 && $this->getStatus() == 3 && !$this->getData('is_created_by_recurring')) {
            $this->sendMailProcessPaymentToAccount();
        }

        if ($this->getId() && $this->getOrigData('status') < 3 && $this->getStatus() == 3) {
            $this->addComment(__('Complete Withdrawal'));
        }
        if (!$this->getData('is_reduced_balance') && $this->getStatus() && $this->getStatus() < 4 &&
            ($this->getStatus() == 3 || $this->_helperConfig->getPaymentConfig('reduce_balance')) ) {
            // reduce balance when create payment
            $account = $this->getAffiliateplusAccount();
            if ($account && $account->getId()) {
                try {
                    $account->setBalance($account->getBalance() - $this->getAmount() - $this->getTaxAmount())
                        ->setTotalPaid($account->getTotalPaid() + $this->getAmount() + $this->getTaxAmount());
                    $commissionReceived = $this->getAmount();
                    if (!$this->getIsPayerFee()) {
                        $commissionReceived -= $this->getFee();
                    }
                    $account->setTotalCommissionReceived($account->getTotalCommissionReceived() + $commissionReceived)
                        ->save();
                    $this->setData('is_reduced_balance', 1);
                } catch (\Exception $e) {

                }
            }
        }
        if ($this->getData('is_reduced_balance') && $this->getStatus() == 4 && !$this->getData('is_refund_balance')){
            // cancel payment -> update affilate account balance
            $account = $this->getAffiliateplusAccount();
            if ($account && $account->getId()) {
                try {
                    $account->setBalance($account->getBalance() + $this->getAmount() + $this->getTaxAmount())
                        ->setTotalPaid($account->getTotalPaid() - $this->getAmount() - $this->getTaxAmount());
                    $commissionReceived = $this->getAmount();
                    if (!$this->getIsPayerFee()) {
                        $commissionReceived -= $this->getFee();
                    }
                    $account->setTotalCommissionReceived($account->getTotalCommissionReceived() - $commissionReceived)
                        ->save();
                    $this->setData('is_refund_balance', 1);
                } catch (\Exception $e) {

                }
                if ($this->getId()) {
                    $this->addComment(__('Cancel Withdrawal'));
                }
            }
        }
        return parent::beforeSave();
    }

    /**
     * @return $this
     */
    public function applyTax() {
        if ($this->getData('applied_tax_calculation')) {
            return $this;
        }
        $helper = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Payment\Tax');
        $taxAmount = $helper->getTaxAmount(
            $this->getAmount(), $this->getIsPayerFee() ? 0 : $this->getFee(), $this->getAffiliateplusAccount(), $this->_getStore()
        );
        $this->setData('tax_amount', $taxAmount)
            ->setData('amount_incl_tax', $this->getAmount() + $taxAmount);
        $this->setData('applied_tax_calculation', true);
        return $this;
    }

    /**
     * @return mixed
     */
    public function afterSave() {
        if ($this->isObjectNew()) {
            if ($this->getData('is_reduced_balance') && $this->getStatus() == 3) {
                $title = __('Create and complete Withdrawal');
            } else {
                $title = __('Create Withdrawal');
            }
            $this->addComment($title);
        } else if (!$this->getData('is_reduced_balance') && $this->getStatus() == 4
        ) {
            $this->addComment(__('Cancel Withdrawal'));
        }
        return parent::afterSave();
    }

    /**
     * @return mixed
     */
    public function getAffiliateplusAccount() {
        if (!$this->_affiliateplus_account) {
            $account = $this->_getAffiliateAccountModel()
                ->setStoreViewId($this->_getStore()->getId())
                ->load($this->getAccountId());
//            $account = $this->getModel('Magestore\Affiliateplus\Model\Session')->getAccount();
            $this->_affiliateplus_account = $account;
        }
        return $this->_affiliateplus_account;
    }

    /**
     * @return int
     */
    public function hasWaitingPayment() {
        $payments = $this->getCollection()
            ->addFieldToFilter('account_id', $this->getAccountId())
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('store_ids', ['finset' => $this->_storeManager->getStore()->getId()]);
        return $payments->getSize();
    }

    /**
     * @return int
     */
    public function getFrontendFee(){
       if ($this->getIsPayerFee()){
           $fee = 0;
       } else{
           $fee = $this->getFee();
       }
        return $fee;
    }

    /**
     * @return bool
     */
    public function isRequest() {
        if ($this->getIsRequest()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Send email to administrator when the affiliate request payment
     * @return $this
     */
    public function sendMailRequestPaymentToSales() {
        if (!$this->_helperConfig->getEmailConfig('is_sent_email_sales_request_payment')){
            return $this;
        }

        $store = $this->_storeManager->getStore();
        $account = $this->_getAffiliateAccountModel()
            ->setStoreId($store->getId())
            ->load($this->getAccountId());

        $sales = $this->_helper->getConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId());

        $this->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setBalanceFormated($this->_helper->convertCurrency($account->getBalance()))
            ->setRequestPayment($this->_helper->convertCurrency($this->getAmount()))
            ->setRequestDateFormated($this->_helper->formatDate($this->getRequestDate(), \IntlDateFormatter::MEDIUM))
            ->setSalesName($sales['name'])
            ->setBackendUrl($this->_helper->getBackendUrl())
        ;
        $template = $this->_helper->getConfig(self::XML_PATH_REQUEST_PAYMENT_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(['payment' => $this, 'store' => $store, 'sender_name' => $sender['name']])
            ->setFrom($sender)
            ->addTo($sales['email'], $sales['name'])
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email notification to affiliate when the payment is processed
     * @return $this
     */
    public function sendMailProcessPaymentToAccount() {
        $store = $this->_getStore();

        $account = $this->getAffiliateplusAccount();

        $whoPayFees = $this->_helperConfig->getPaymentConfig('who_pay_fees');

        if ($whoPayFees == 'payer'){
            $payAmount = $this->getAmount();
        }
        else{
            $payAmount = $this->getAmount() - $this->getFee();
        }
        $now = new \DateTime();
        $this->addPaymentInfo()->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setAccountPaypalEmail($account->getPaypalEmail())
            ->setBalanceFormated($this->_helper->convertCurrency($account->getBalance() - $this->getAmount()))
            ->setRequestPayment($this->_helper->convertCurrency($this->getAmount()))
            ->setPayPayment($this->_helper->convertCurrency($payAmount))
            ->setFeeFormated($this->_helper->convertCurrency($this->getFrontendFee()))
            ->setCreatedTimeFormated($this->_helper->formatDate($now, \IntlDateFormatter::MEDIUM))
            ->setRequestDateFormated($this->_helper->formatDate($this->getRequestDate(), \IntlDateFormatter::MEDIUM))
        ;

        $template = $this->_helper->getConfig(self::XML_PATH_PROCESS_PAYMENT_EMAIL, $store->getId());
        $sender = $this->_helper->getSenderContact();

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()])
            ->setTemplateVars(
                [
                    'payment' => $this,
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

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function _getStore() {
        $storeIds = $this->getStoreIds();
        if (strpos($storeIds, ',')){
            $store = $this->_storeManager->getStore();
        } else{
            $store = $this->_storeManager->getStore(intval($storeIds));
        }

        return $store;
    }

    /**
     * @return bool
     */
    public function isMultiStore() {
        $storeIds = $this->getStoreIds();
        if (strpos($storeIds, ',')){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $comment
     */
    public function addComment($comment) {
        try {
            $now = new \DateTime();
            $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\History')
                ->setData(
                    [
                        'payment_id' => $this->getId(),
                        'status' => $this->getData('status'),
                        'created_time' => $now,
                        'description' => $comment,
                    ]
                )
                ->setId(null)->save();
        } catch (\Exception $e) {

        }
    }

    /**
     * @return mixed
     */
    public function canRestore() {
        return $this->getData('payment_is_deleted');
    }

    /**
     * @return mixed
     */
    protected function _getAffiliateAccountModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
    }
}
