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
namespace Magestore\Affiliateplus\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
/**
 * Class Tab GeneralTab
 */
class Abtractblock extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\AccountValue\CollectionFactory
     */
    protected $_accountValueCollectionFactory;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;
    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_configHelper;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeModel;
    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Website
     */
    protected $_websiteCustomer;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\Payment
     */
    protected $_helperPayment;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Tab constructor
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Framework\Data\FormFactory       $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Customer\Model\Customer\Attribute\Source\Website $websiteCustomer,
        \Magento\Store\Model\StoreFactory $storeModel,
        \Magestore\Affiliateplus\Helper\Config $configHelper,
        \Magestore\Affiliateplus\Model\ResourceModel\Action\CollectionFactory $actionCollectionFactory,
        \Magestore\Affiliateplus\Model\ResourceModel\AccountValue\CollectionFactory  $accountValueCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magestore\Affiliateplus\Helper\Payment $helperPayment,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_eventManager = $context->getEventManager();
        $this->_yesno = $yesno;
        $this->_storeManager = $context->getStoreManager();
        $this->_websiteCustomer = $websiteCustomer;
        $this->_storeModel = $storeModel;
        $this->_configHelper = $configHelper;
        $this->_actionCollectionFactory =  $actionCollectionFactory;
        $this->_accountValueCollectionFactory = $accountValueCollectionFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->_sessionQuote = $sessionQuote;
        $this->_helperPayment = $helperPayment;
        $this->_objectManager = $objectManager;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout() {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magestore\Affiliateplus\Block\Adminhtml\Form\Account\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );
    }
    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
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
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('affiliate_account');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Abtract Block');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Abtract Block');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }


    /**
     * Convert price
     *
     * @param float $value
     * @param bool $format
     * @return float
     */
    public function convertPrice($value, $format = true)
    {
        return  $this->_priceCurrency->convert($value, $this->getStore());
    }

}
