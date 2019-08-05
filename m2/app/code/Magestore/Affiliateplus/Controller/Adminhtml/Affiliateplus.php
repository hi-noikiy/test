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
namespace Magestore\Affiliateplus\Controller\Adminhtml;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Action Account
 */
abstract class Affiliateplus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory
     */
    protected $_accountCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Model\AccountFactory
     */
    protected $_accountFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Magestore\Affiliateplus\Model\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * @var \Magestore\Affiliateplus\Model\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_helperConfig;



    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * Action constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Magestore\Affiliateplus\Model\BannerFactory $bannerFactory,
        \Magestore\Affiliateplus\Model\PaymentFactory $paymentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Backend\Model\Session\Quote $sessionQuote
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerFactory = $customerFactory;
        $this->_accountFactory =  $accountFactory;
        $this->_accountCollectionFactory = $accountCollectionFactory;
        $this->_eventManager = $context->getEventManager();
        $this->_storeManager = $storeManager;
        $this->_bannerFactory = $bannerFactory;
        $this->_paymentFactory = $paymentFactory;
        $this->_helper = $helper;
        $this->_helperConfig = $helperConfig;
        $this->_priceCurrency = $priceCurrency;
        $this->_sessionQuote = $sessionQuote;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Affiliateplus::magestoreaffiliateplus');
    }
    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_getSession()->getStore();
    }
    /**
     * @param $value
     * @return mixed
     */
    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
}
