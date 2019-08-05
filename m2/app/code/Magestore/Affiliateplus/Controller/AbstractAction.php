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
namespace Magestore\Affiliateplus\Controller;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
/**
 * Class AbstractAction
 * @package Magestore\Affiliateplus\Controller
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;
    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    protected $_fieldsetConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magestore\Affiliateplus\Helper\Cookie
     */
    protected $_cookieHelper;
    /**
     * @var \Magestore\Affiliateplus\Model\Session
     */
    protected $_affiliateSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_getUrl;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directoryList;
    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_getSession;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerHelperData;
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * Action constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magestore\Affiliateplus\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magestore\Affiliateplus\Helper\Account $accountHelper,
        \Magento\Customer\Model\Session $sessionCustomer,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magestore\Affiliateplus\Model\Session $affiliateSession,
        \Magestore\Affiliateplus\Helper\Cookie $cookieHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $customerHelperData,
        PriceCurrencyInterface $priceCurrency

    ) {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $context->getObjectManager();
        $this->_eventManager = $context->getEventManager();
        $this->_getSession = $session;
        $this->_accountHelper = $accountHelper;
        $this->_sessionCustomer = $sessionCustomer;
        $this->_directoryList = $directoryList;
        $this->_getUrl = $context->getUrl();
        $this->_affiliateSession = $affiliateSession;
        $this->_cookieHelper = $cookieHelper;
        $this->_coreRegistry = $registry;
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_dateFilter = $dateFilter;
        $this->_blockFactory = $blockFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerHelperData = $customerHelperData;
        $this->_priceCurrency = $priceCurrency;

    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    public function getBaseDir(){
        return $this->_directoryList->getRoot();
    }
    /**
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    public function getCoreSession()
    {
        return $this->_getSession;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession(){
        return $this->_sessionCustomer;
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Session
     */
    public function getSession()
    {
        return $this->_affiliateSession;
    }
    /**
     * get Base Url Media
     *
     * @return mixed
     */
    public function getBaseUrlMedia()
    {
        return $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);;
    }
    /**
     * @return mixed
     */
    public function isRegistered(){
        return $this->getAccountHelper()->isRegistered();
    }

    /**
     * @return mixed
     */
    public function accountNotLogin(){
        return $this->getAccountHelper()->accountNotLogin();
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Account
     */
    public function getAccountHelper(){
        return $this->_accountHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
       return $this->_storeManager;
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Payment
     */
    public function getPaymentHelper(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Helper\Payment');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment\Verify
     */
    public function getModelPaymentVerify()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify');
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment
     */
    public function getModelPayment(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Account
     */
    public function getModelAccount(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
    }

    /**
     * @return \Magestore\Affiliateplus\Block\AbstractTemplate
     */
    public function getAbtracTemplate()
    {
        return $this->_objectManager->get('Magestore\Affiliateplus\Block\AbstractTemplate');
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config');
    }
    /**
     * @param $value
     * @return float
     */
    public function convertPrice($value, $format = true)
    {
        return $this->_priceCurrency->convert($value, $format);
    }
}
