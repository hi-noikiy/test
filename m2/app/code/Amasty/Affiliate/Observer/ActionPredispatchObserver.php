<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Observer;

use Amasty\Affiliate\Api\BannerRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ActionPredispatchObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Amasty\Affiliate\Api\LinksRepositoryInterface
     */
    private $linksRepository;

    /**
     * @var \Amasty\Affiliate\Model\LinksFactory
     */
    private $linksFactory;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * ActionPredispatchObserver constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Api\LinksRepositoryInterface $linksRepository
     * @param BannerRepositoryInterface $bannerRepository
     * @param \Amasty\Affiliate\Model\LinksFactory $linksFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Api\LinksRepositoryInterface $linksRepository,
        \Amasty\Affiliate\Api\BannerRepositoryInterface $bannerRepository,
        \Amasty\Affiliate\Model\LinksFactory $linksFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->accountRepository = $accountRepository;
        $this->linksRepository = $linksRepository;
        $this->linksFactory = $linksFactory;
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getRequest();
        $affiliateUlrParameter = $this->scopeConfig->getValue('amasty_affiliate/url/parameter');
        $accountCode = $request->getParam($affiliateUlrParameter);
        if (!empty($accountCode)) {
            /** @var \Amasty\Affiliate\Model\Account $account */
            $account = $this->accountRepository->getByReferringCode($accountCode);
            if ($account->getIsAffiliateActive()) {
                $account->addToCookies();
                /** @var \Amasty\Affiliate\Model\Links $link */
                $link = $this->linksFactory->create();
                $data = [
                    'link_type' =>  $request->getParam('referring_service'),
                    'affiliate_account_id' => $account->getAccountId()
                ];
                if ($request->getParam('element_id')) {
                    $data['element_id'] = $request->getParam('element_id');
                }

                if ($data['link_type'] == \Amasty\Affiliate\Model\Links::TYPE_BANNER) {
                    /** @var \Amasty\Affiliate\Model\Banner $banner */
                    $banner = $this->bannerRepository->get($data['element_id']);
                    $banner->setClicks($banner->getClickCount($data['affiliate_account_id']));
                    $this->bannerRepository->save($banner);
                }

                $link->addData($data);
                $this->linksRepository->save($link);

                $url = $request->getOriginalPathInfo();
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
            }
        }

        return $this;
    }
}
