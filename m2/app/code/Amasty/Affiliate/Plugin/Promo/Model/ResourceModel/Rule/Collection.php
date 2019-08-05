<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\Promo\Model\ResourceModel\Rule;

use Amasty\Affiliate\Model\RegistryConstants;
use Magento\Framework\DB\Select;
use Magento\Checkout\Model\Session as CheckoutSession;

class Collection
{
    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection
     */
    private $programCollection;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * Collection constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programCollection
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Magento\Framework\Registry $registry
     * @param CheckoutSession $checkoutSession
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programCollection,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\Registry $registry,
        CheckoutSession $checkoutSession,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
    ) {
        $this->programCollection = $programCollection;
        $this->cookieManager = $cookieManager;
        $this->request = $request;
        $this->accountRepository = $accountRepository;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->accountCollectionFactory = $accountCollectionFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     */
    public function beforeLoad(
        \Magento\SalesRule\Model\ResourceModel\Rule\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        if ($this->isAffiliate()) {
            /** @var Select $select */
            $select = $subject->getSelect();
            $whereParts = $select->getPart(Select::WHERE);

            $affiliateRuleIds = $this->programCollection
                ->addFieldToFilter('main_table.is_active', ['eq' => 1])
                ->getColumnValues('rule_id');
            $affiliateRuleIds = join("','", $affiliateRuleIds);
            foreach ($whereParts as $key => $wherePart) {
                if ($wherePart == "AND (`is_active` = '1')") {
                    $whereParts[$key] = "AND ((`is_active` = '1') OR main_table.rule_id IN ('$affiliateRuleIds'))";
                }
                if ($wherePart == "AND (`main_table`.`coupon_type` = '1')") {
                    $whereParts[$key] = "AND (`main_table`.`coupon_type` = '1') 
                    OR main_table.rule_id IN ('$affiliateRuleIds')";
                }
            }

            $select->setPart(Select::WHERE, $whereParts);
        }
    }

    /**
     * @return bool
     */
    public function isAffiliate()
    {
        $isAffiliate = false;

        $couponCode = $this->checkoutSession->getQuote()->getCouponCode();

        if (!empty($couponCode)) {
            /** @var \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection */
            $couponCollection = $this->couponCollectionFactory->create();
            $couponCollection->addCouponFilter($couponCode);
            if ($couponCollection->getSize() > 0) {
                /** @var \Amasty\Affiliate\Model\Account $account */
                $account = $this->accountRepository->getByCouponCode($couponCode);
                if ($account->getAccountId() != null && $account->getIsAffiliateActive()) {
                    $isAffiliate = true;
                }
            }
        } else {
            $affiliateCode = $this->cookieManager
                ->getCookie(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE);
            if ($affiliateCode !== null) {
                /** @var \Amasty\Affiliate\Model\ResourceModel\Account\Collection $accountCollection */
                $accountCollection = $this->accountCollectionFactory->create();
                $accountCollection->addCodeFilter($affiliateCode);
                if ($accountCollection->getSize() > 0) {
                    /** @var \Amasty\Affiliate\Model\Account $account */
                    $account = $this->accountRepository->getByReferringCode($affiliateCode);

                    if ($affiliateCode != null && $account->getIsAffiliateActive()) {
                        $isAffiliate = true;
                    }
                }
            }
        }

        return $isAffiliate;
    }
}
