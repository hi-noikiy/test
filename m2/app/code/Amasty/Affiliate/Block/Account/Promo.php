<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

use Magento\Framework\View\Element\Template;

class Promo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var string
     */
    protected $_template = 'account/promo.phtml';

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection
     */
    private $couponCollection;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Banner\Collection
     */
    private $bannerCollection;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * Promo constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection
     * @param \Amasty\Affiliate\Model\ResourceModel\Banner\Collection $bannerCollection
     * @param \Amasty\Affiliate\Model\Account $account
     * @param \Magento\Framework\Url\Helper\Data $ulrHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Amasty\Affiliate\Model\ResourceModel\Banner\Collection $bannerCollection,
        \Amasty\Affiliate\Model\Account $account,
        \Magento\Framework\Url\Helper\Data $ulrHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->accountRepository = $accountRepository;
        $this->couponCollection = $couponCollection;
        $this->bannerCollection = $bannerCollection;
        $this->account = $account;
        $this->scopeConfig = $context->getScopeConfig();
        $this->urlHelper = $ulrHelper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Affiliate Programs'));
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getCoupons()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }

        $account = $this->accountRepository->getByCustomerId($customerId);
        $this->couponCollection->addAccountIdFilter($account->getAccountId());
        $this->couponCollection->addProgramActiveFilter();

        return $this->couponCollection;
    }

    public function getBanners()
    {
        return $this->bannerCollection->addStatusFilter();
    }

    public function getLinkParams()
    {
        $codeKey = $this->_scopeConfig->getValue('amasty_affiliate/url/parameter');
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getCurrentAccount();
        $code = $account->getReferringCode();

        $linkParams = $codeKey . '='. $code . '&referring_service=' . \Amasty\Affiliate\Model\Links::TYPE_LINK;

        return $linkParams;
    }

    /**
     * @param \Amasty\Affiliate\Model\Banner $banner
     * @return string
     */
    public function getRelNofollow($banner)
    {
        $relNofollow = '';
        if ($banner->getRelNoFollow()) {
            $relNofollow = "rel='nofollow'";
        }

        return $relNofollow;
    }

    /**
     * @param \Amasty\Affiliate\Model\Banner $banner
     * @return string
     */
    public function getBannerLink($banner)
    {
        $accountCode = $this->accountRepository->getCurrentAccount()->getReferringCode();
        $codeParameter = $this->scopeConfig->getValue('amasty_affiliate/url/parameter');

        $params = [
            $codeParameter => $accountCode,
            'referring_service' => 'banner',
            'element_id' => $banner->getBannerId()
        ];

        $bannerLink = $this->urlHelper->addRequestParam($banner->getLink(), $params);

        return $bannerLink;
    }
}
