<?php

/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class AbstractHelper
 * @package Magestore\Affiliateplusprogram\Helper
 */
class AbstractHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Affiliateplus\Helper\HelperAbstract $helperAbstract
     */
    protected $_helperAbstract;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data $helperData
     */
    protected $_helperData;

    /**
     * @var \Magestore\Affiliateplus\Helper\Account $helperAccount
     */
    protected $_helperAccount;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory
     */
    protected $_programFactory;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\JoinedFactory $joinedFactory
     */
    protected $_joinedFactory;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\AccountFactory $programAccountFactory
     */
    protected $_programAccountFactory;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\CategoryFactory $programCategoryFactory
     */
    protected $_programCategoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $productFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_checkoutCart;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_backendQuoteSession;
    /**
     * @var \Magestore\Affiliateplusprogram\Model\ResourceModel\Program\CollectionFactory
     */
    protected $_programCollectionFactory;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * AbstractHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Affiliateplus\Helper\HelperAbstract $helperAbstract
     * @param \Magestore\Affiliateplus\Helper\Data $helperData
     * @param \Magestore\Affiliateplus\Helper\Account $helperAccount
     * @param \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory
     * @param \Magestore\Affiliateplusprogram\Model\ResourceModel\Program\CollectionFactory $programCollectionFactory
     * @param \Magestore\Affiliateplusprogram\Model\JoinedFactory $joinedFactory
     * @param \Magestore\Affiliateplusprogram\Model\AccountFactory $programAccountFactory
     * @param \Magestore\Affiliateplusprogram\Model\CategoryFactory $programCategoryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Affiliateplus\Helper\HelperAbstract $helperAbstract,
        \Magestore\Affiliateplus\Helper\Data $helperData,
        \Magestore\Affiliateplus\Helper\Account $helperAccount,
        \Magestore\Affiliateplusprogram\Model\ProgramFactory $programFactory,
        \Magestore\Affiliateplusprogram\Model\ResourceModel\Program\CollectionFactory $programCollectionFactory,
        \Magestore\Affiliateplusprogram\Model\JoinedFactory $joinedFactory,
        \Magestore\Affiliateplusprogram\Model\AccountFactory $programAccountFactory,
        \Magestore\Affiliateplusprogram\Model\CategoryFactory $programCategoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Checkout\Model\Cart $checkoutCart,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_helperAbstract = $helperAbstract;
        $this->_helperData = $helperData;
        $this->_helperAccount = $helperAccount;
        $this->_programFactory = $programFactory;
        $this->_joinedFactory = $joinedFactory;
        $this->_programAccountFactory = $programAccountFactory;
        $this->_programCategoryFactory = $programCategoryFactory;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_checkoutCart = $checkoutCart;
        $this->_backendQuoteSession = $backendQuoteSession;
        $this->_programCollectionFactory = $programCollectionFactory;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context);
    }
    /**
     * @param $value
     * @param bool|true $format
     * @return mixed
     */
    public function convertPrice($value, $format = true, $store = null)
    {
        if(!$store){
            $store = $this->getStore();
        }
        return $this->_priceCurrency->convert($value, $store);
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