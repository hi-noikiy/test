<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * @method \Amasty\Affiliate\Model\ResourceModel\Account getResource()
 * Class Account
 * @package Amasty\Affiliate\Model
 */
class Account extends \Magento\Framework\Model\AbstractModel implements AccountInterface
{
    const WIDGET_TYPE_BESTSELLER = 'bestseller';
    const WIDGET_TYPE_NEW = 'new';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var ResourceModel\Coupon
     */
    private $affiliateCouponResource;

    /**
     * @var ResourceModel\Program\CollectionFactory
     */
    private $programCollectionFactory;

    /**
     * @var CouponFactory
     */
    private $affiliateCouponFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $currency;

    /**
     * @var Mailsender
     */
    private $mailsender;

    /**
     * Account constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param ResourceModel\Coupon $affiliateCouponResource
     * @param \Amasty\Affiliate\Model\CouponFactory $affiliateCouponFactory
     * @param ResourceModel\Program\CollectionFactory $programCollectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param Mailsender $mailsender
     * @param AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\ResourceModel\Coupon $affiliateCouponResource,
        \Amasty\Affiliate\Model\CouponFactory $affiliateCouponFactory,
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $programCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Amasty\Affiliate\Model\Mailsender $mailsender,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->ruleRepository = $ruleRepository;
        $this->scopeConfig = $scopeConfig;
        $this->accountRepository = $accountRepository;
        $this->affiliateCouponResource = $affiliateCouponResource;
        $this->affiliateCouponFactory = $affiliateCouponFactory;
        $this->programCollectionFactory = $programCollectionFactory;
        $this->request = $request;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->currency = $currency;
        $this->mailsender = $mailsender;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Affiliate\Model\ResourceModel\Account');
        $this->setIdFieldName('account_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailable()
    {
        return $this->getBalance() - $this->getOnHold();
    }

    /**
     * @param $couponCode
     * @return $this
     */
    public function loadByCouponCode($couponCode)
    {
        $this->getResource()->loadBy($this, $couponCode, 'code');
        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function loadByReferringCode($code)
    {
        $this->getResource()->loadBy($this, $code, 'referring_code');
        return $this;
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        $this->getResource()->loadBy($this, $customerId, 'customer_id');
        return $this;
    }

    /**
     * @param int $customerId
     * @param array $data
     * @return $this
     */
    public function createAccount($customerId, $data)
    {
        $this->addData($data);
        $this->setCustomerId($customerId);
        $this->setAcceptedTermsConditions(true);
        $this->generateReferringCode();
        $this->accountRepository->save($this);
        $this->addCoupon();
        if ($this->scopeConfig->getValue('amasty_affiliate/email/admin/new_affiliate')) {
            $this->_sendAdminNotification();
        }
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/welcome')) {
            $this->_sendAffiliateNotification();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addCoupon()
    {
        /** @var ResourceModel\Program\Collection $programCollection */
        $programCollection = $this->programCollectionFactory->create();
        /** @var \Amasty\Affiliate\Model\Program $program */
        foreach ($programCollection as $program) {
            /** @var \Amasty\Affiliate\Model\Coupon $coupon */
            $coupon = $this->affiliateCouponFactory->create();
            $coupon->addCoupon($program, $this->getAccountId());
        }

        return $this;
    }

    /**
     * Generate referring code for affiliate account
     */
    public function generateReferringCode()
    {
        $randomString = $this->generateRandomString($this->getCodeLength());
        $this->setReferringCode($randomString);
    }

    /**
     * Add current affiliate referring code to cookies
     */
    public function addToCookies()
    {
        $cookieExpiration = $this->scopeConfig
                ->getValue('amasty_affiliate/general/cookie_expiration') * 24 * 60 * 60;//in seconds
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($cookieExpiration)
            ->setPath('/')
            ->setSecure($this->request->isSecure());
        $this->cookieManager->setPublicCookie(
            \Amasty\Affiliate\Model\RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE,
            $this->getReferringCode(),
            $publicCookieMetadata
        );
    }

    /**
     * @param $length
     * @return string
     */
    public function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        //checking for unique code
        /** @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection $collection */
        $collection = $this->getResourceCollection();
        $collection->addFieldToFilter('referring_code', ['eq' => $randomString]);
        if ($collection->getSize() > 0) {
            $randomString = $this->generateRandomString($length);
        }

        return $randomString;
    }

    /**
     * Add currency and format
     */
    public function preparePrices()
    {
        $priceFields = [
            'balance',
            'available',
            'on_hold',
            'commission_paid',
            'lifetime_commission'
        ];

        $priceData = [];

        foreach ($priceFields as $field) {
            $priceData[$field . '_with_currency'] = $this->convertToPrice($this->getData($field));
            if ($field == 'available') {
                $priceData[$field . '_with_currency'] = $this->convertToPrice($this->getData('balance') - $this->getData('available'));
            }
        }

        $this->addData($priceData);
    }

    /**
     * Add currency and format
     * @param $value
     * @return string
     */
    public function convertToPrice($value)
    {
        if (is_numeric($value)) {
            $store = $this->storeManager->getStore();
            $currency = $this->currency->getCurrency($store->getBaseCurrencyCode());
            $value = $currency->toCurrency(sprintf("%f", $value));
        }

        return $value;
    }

    /**
     * @param $status
     */
    public function sendAffiliateStatusEmail($status)
    {
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/account_status')
            && $this->getReceiveNotifications()
        ) {
            $emailData = $this->getData();
            $emailData['name'] = $this->getFirstname() . ' ' . $this->getLastname();
            $emailData['status'] = $status == 1 ? __('Active') : __('Inactive');
            $sendToMail = $this->getEmail();

            $this->mailsender->sendAffiliateMail($emailData, Mailsender::TYPE_AFFILIATE_STATUS, $sendToMail, $this);
        }
    }

    /**
     * Send email notification to admin about new affiliate account
     */
    protected function _sendAdminNotification()
    {
        /** @var Account $account */
        $account = $this->accountRepository->get($this->getAccountId());
        $emailData = $account->getData();
        $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();
        $sendToMail = $this->scopeConfig->getValue('amasty_affiliate/email/general/recipient_email');

        $this->mailsender->sendMail($emailData, Mailsender::TYPE_ADMIN_NEW_ACCOUNT, $sendToMail);
    }

    /**
     * Send email notification to affiliate about creating of account
     */
    protected function _sendAffiliateNotification()
    {
        /** @var Account $account */
        $account = $this->accountRepository->get($this->getAccountId());
        $emailData = $account->getData();
        $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();
        $sendToMail = $account->getEmail();

        $this->mailsender->sendAffiliateMail($emailData, Mailsender::TYPE_AFFILIATE_WELCOME, $sendToMail, $account);
    }

    /**
     * Get code length for affiliate url parameter
     * @return int|mixed
     */
    protected function getCodeLength()
    {
        $length = $this->scopeConfig->getValue('amasty_affiliate/url/length');

        if ($length < 4 || $length > 31) {
            $length = 10;
        }

        return $length;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountId()
    {
        return $this->_getData(AccountInterface::ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAccountId($accountId)
    {
        $this->setData(AccountInterface::ACCOUNT_ID, $accountId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData(AccountInterface::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        $this->setData(AccountInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAffiliateActive()
    {
        return $this->_getData(AccountInterface::IS_AFFILIATE_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAffiliateActive($isAffiliateActive)
    {
        $this->setData(AccountInterface::IS_AFFILIATE_ACTIVE, $isAffiliateActive);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAcceptedTermsConditions()
    {
        return $this->_getData(AccountInterface::ACCEPTED_TERMS_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAcceptedTermsConditions($acceptedTermsConditions)
    {
        $this->setData(AccountInterface::ACCEPTED_TERMS_CONDITIONS, $acceptedTermsConditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReceiveNotifications()
    {
        return $this->_getData(AccountInterface::RECEIVE_NOTIFICATIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setReceiveNotifications($receiveNotifications)
    {
        $this->setData(AccountInterface::RECEIVE_NOTIFICATIONS, $receiveNotifications);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaypalEmail()
    {
        return $this->_getData(AccountInterface::PAYPAL_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaypalEmail($paypalEmail)
    {
        $this->setData(AccountInterface::PAYPAL_EMAIL, $paypalEmail);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringCode()
    {
        return $this->_getData(AccountInterface::REFERRING_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferringCode($referringCode)
    {
        $this->setData(AccountInterface::REFERRING_CODE, $referringCode);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringWebsite()
    {
        return $this->_getData(AccountInterface::REFERRING_WEBSITE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferringWebsite($referringWebsite)
    {
        $this->setData(AccountInterface::REFERRING_WEBSITE, $referringWebsite);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        return $this->_getData(AccountInterface::BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($balance)
    {
        $this->setData(AccountInterface::BALANCE, $balance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnHold()
    {
        return $this->_getData(AccountInterface::ON_HOLD);
    }

    /**
     * {@inheritdoc}
     */
    public function setOnHold($onHold)
    {
        $this->setData(AccountInterface::ON_HOLD, $onHold);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionPaid()
    {
        return $this->_getData(AccountInterface::COMMISSION_PAID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionPaid($commissionPaid)
    {
        $this->setData(AccountInterface::COMMISSION_PAID, $commissionPaid);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLifetimeCommission()
    {
        return $this->_getData(AccountInterface::LIFETIME_COMMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLifetimeCommission($lifetimeCommission)
    {
        $this->setData(AccountInterface::LIFETIME_COMMISSION, $lifetimeCommission);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetWidth()
    {
        return $this->_getData(AccountInterface::WIDGET_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetWidth($widgetWidth)
    {
        $this->setData(AccountInterface::WIDGET_WIDTH, $widgetWidth);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetHeight()
    {
        return $this->_getData(AccountInterface::WIDGET_HEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetHeight($widgetHeight)
    {
        $this->setData(AccountInterface::WIDGET_HEIGHT, $widgetHeight);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetTitle()
    {
        return $this->_getData(AccountInterface::WIDGET_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetTitle($widgetTitle)
    {
        $this->setData(AccountInterface::WIDGET_TITLE, $widgetTitle);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetProductsNum()
    {
        return $this->_getData(AccountInterface::WIDGET_PRODUCTS_NUM);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetProductsNum($widgetProductsNum)
    {
        $this->setData(AccountInterface::WIDGET_PRODUCTS_NUM, $widgetProductsNum);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetType()
    {
        return $this->_getData(AccountInterface::WIDGET_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetType($widgetType)
    {
        $this->setData(AccountInterface::WIDGET_TYPE, $widgetType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetShowName()
    {
        return $this->_getData(AccountInterface::WIDGET_SHOW_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetShowName($widgetShowName)
    {
        $this->setData(AccountInterface::WIDGET_SHOW_NAME, $widgetShowName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetShowPrice()
    {
        return $this->_getData(AccountInterface::WIDGET_SHOW_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWidgetShowPrice($widgetShowPrice)
    {
        $this->setData(AccountInterface::WIDGET_SHOW_PRICE, $widgetShowPrice);

        return $this;
    }
}
