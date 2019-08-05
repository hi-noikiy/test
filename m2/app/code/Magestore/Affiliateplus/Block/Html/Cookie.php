<?php
namespace Magestore\Affiliateplus\Block\Html;


use Magento\Framework\Pricing\PriceCurrencyInterface;

class Cookie extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\Cookie
     */
    protected $_cookieHelper;

    /**
     * Cookie constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Affiliateplus\Helper\Config $configHelper
     * @param \Magestore\Affiliateplus\Helper\Data $dataHelper
     * @param \Magestore\Affiliateplus\Helper\Account $accountHelper
     * @param \Magento\Cms\Model\Page $pageModel
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magestore\Affiliateplus\Model\Session $sessionModel
     * @param \Magento\Customer\Model\Session $sessionCustomer
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Helper\Data $helperDirectory
     * @param \Magestore\Affiliateplus\Helper\Payment $paymentHelper
     * @param \Magestore\Affiliateplus\Model\AccountFactory $accountFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magestore\Affiliateplus\Helper\Payment\Tax $taxHelper
     * @param \Magento\Checkout\Model\Session $sessionCheckout
     * @param \Magento\Framework\Locale\Format $formatLocale
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\Affiliateplus\Helper\Cookie $cookieHelper
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Magestore\Affiliateplus\Helper\Config $configHelper,
                                \Magestore\Affiliateplus\Helper\Data $dataHelper,
                                \Magestore\Affiliateplus\Helper\Account $accountHelper,
                                \Magento\Cms\Model\Page $pageModel,
                                \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
                                \Magento\Directory\Helper\Data $directoryHelper,
                                \Magestore\Affiliateplus\Model\Session $sessionModel,
                                \Magento\Customer\Model\Session $sessionCustomer,
                                \Magento\Customer\Model\AddressFactory $addressFactory,
                                \Magento\Framework\View\Element\BlockFactory $blockFactory,
                                \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
                                \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
                                \Magento\Directory\Helper\Data $helperDirectory,
                                \Magestore\Affiliateplus\Helper\Payment $paymentHelper,
                                \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
                                PriceCurrencyInterface $priceCurrency,
                                \Magestore\Affiliateplus\Helper\Payment\Tax $taxHelper,
                                \Magento\Checkout\Model\Session $sessionCheckout,
                                \Magento\Framework\Locale\Format $formatLocale,
                                \Magento\Framework\Json\Helper\Data $jsonHelper,
                                \Magento\Backend\Model\Session\Quote $sessionQuote,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\ResourceConnection $resourceConnection,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Framework\App\Cache\Type\Config $configCacheType,
                                \Magento\Cms\Model\Template\FilterProvider $filterProvider,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                \Magestore\Affiliateplus\Helper\Cookie $cookieHelper,
                                array $data)
    {
        $this->_eventManager = $context->getEventManager();
        $this->_cookieHelper = $cookieHelper;
        parent::__construct($context, $configHelper, $dataHelper, $accountHelper, $pageModel, $localeCurrency, $directoryHelper, $sessionModel, $sessionCustomer, $addressFactory, $blockFactory, $regionCollectionFactory, $countryCollectionFactory, $helperDirectory, $paymentHelper, $accountFactory, $priceCurrency, $taxHelper, $sessionCheckout, $formatLocale, $jsonHelper, $sessionQuote, $registry, $objectManager, $resourceConnection, $checkoutSession, $configCacheType, $filterProvider, $resultPageFactory, $data);
    }

    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled())
            return $this;
        $request     = $this->getRequest();
        $accountCode = $request->getParam('acc');
        if ($accountCode) {
            $this->_eventManager->dispatch('affiliateplus_controller_action_predispatch',
                [
                    'request' => $request
                ]
            );
            $this->_saveClickAction($request);
            $expiredTime = $this->_configHelper->getGeneralConfig('expired_time');
            $accountData = $this->_getAffiliateAccountByAccountCode($accountCode, $request);
            $account     = isset($accountData['account']) ? $accountData['account'] : null;
            if ($account && $account->getId()) {
                $accountCode = $account->getIdentifyCode();
                $this->_cookieHelper->saveCookie($accountCode, $expiredTime, false);
            }
        }
    }

    /**
     * @param $request
     */
    protected
    function _saveClickAction($request)
    {
        $accountCode = $request->getParam('acc');
        $accountData = $this->_getAffiliateAccountByAccountCode($accountCode, $request);
        $account     = isset($accountData['account']) ? $accountData['account'] : null;
        if ($account && $account->getId()) {
            $ipAddress = $request->getClientIp();
            $banner_id = $request->getParam('bannerid');
            $storeId   = $this->_storeManager->getStore()->getId();
            if ($banner_id) {
                $banner = $this->_objectManager->create('Magestore\Affiliateplus\Model\Banner')
                    ->load($banner_id);
                $banner->setStoreViewId($storeId);
                if ($banner->getStatus() != 1) {
                    $banner_id = 0;
                }
            }
            $check = false;
            $param = isset($accountData['param']) ? $accountData['param'] : '';
            if ($this->_dataHelper->exitedCookie($param)) {
                return $this;
            }
            if (!$check) {
                if ($this->_dataHelper->isProxy()) {
                    return $this;
                }
            }
            if (!$check) {
                if ($this->_dataHelper->isRobots()) {
                    return $this;
                }
            }

            $domain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if (!$domain && $request->getParam('src')) {
                $domain = $request->getParam('src');
            }
            $landing_page = $request->getOriginalPathInfo();
            $actionModel  = $this->_objectManager->create('Magestore\Affiliateplus\Model\Action');

            if ($check) {
                $isUnique = 0;
            } else {
                $isUnique = $actionModel->checkIpClick($ipAddress, $account->getId(), $domain, $banner_id, 2);
            }
            $action = $actionModel->saveAction($account->getId(), $banner_id, 2, $storeId, $isUnique, $ipAddress, $domain, $landing_page);
            if ($isUnique) {
                if ($this->_configHelper->getActionConfig('detect_iframe')) {
                    $hashCode = md5($action->getCreatedDate() . $action->getId());
                    $this->_sessionCheckout->setData('transaction_checkiframe__action_id', $action->getId());
                    $this->_sessionCheckout->setData('transaction_checkiframe_hash_code', $hashCode);
                } else {
                    $action->setIsUnique(1)->save();
                    $this->_eventManager->dispatch('affiliateplus_save_action_before',
                        [
                            'action'    => $action,
                            'is_unique' => $isUnique,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Get the affiliate account by account code
     * @param $accountCode
     * @param $request
     * @return \Magestore\Affiliateplus\Model\Account
     */
    protected function _getAffiliateAccountByAccountCode($accountCode,
                                                         $request)
    {
        $param = array();
        if (!$accountCode || ($accountCode == '')) {
            $paramList  = $this->_configHelper->getReferConfig('url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $this->getRequest()->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != '')) {
                    $param = $paramArray[$i];
                    break;
                }
            }
        }

        /**
         * fix issue can't detect affiliate when customer click on the affiliate link on Facebook
         */
        if (strpos($accountCode, "?")) {
            $code        = explode("?", $accountCode);
            $accountCode = $code[0];
        }
        if ($this->_configHelper->getGeneralConfig('url_param_value') == 2) {
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'account_id');
        } else {
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'identify_code');
        }

        if ($account->getId()) {
            $accountCode = $account->getIdentifyCode();
        }

        if (!$accountCode) {
            return;
        }

        if ($account = $this->_getAffiliateSession()->getAccount()) {
            if ($account->getIdentifyCode() == $accountCode) {
                return;
            }
        }

        $storeId = $this->_storeManager->getStore()->getId();
        if (!$storeId)
            return;

        $account = $this->_accountFactory->create()->setStoreViewId($storeId)->loadByIdentifyCode($accountCode);
        if (!$account->getId() || ($account->getStatus() != 1)) {
            return;
        }
        $accountData = [
            'param'   => $param,
            'account' => $account
        ];

        return $accountData;
    }

    /**
     * Get Affiliate Session Model
     * @return mixed
     */
    protected function _getAffiliateSession()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Session');
    }
}