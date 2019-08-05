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
 * Model Account
 */
class AbtractModel extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magestore\Affiliateplus\Helper\HelperAbstract
     */
    protected $_helperAbstract;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_helperConfig;

    /**
     * @var \Magestore\Affiliateplus\Helper\Payment
     */
    protected $_helperPayment;

    /**
     * @var \Magestore\Affiliateplus\Helper\Url
     */
    protected $_helperUrl;

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
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * AbtractModel constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     * @param \Magestore\Affiliateplus\Helper\Payment $helperPayment
     * @param \Magestore\Affiliateplus\Helper\Config $helperConfig
     * @param \Magestore\Affiliateplus\Helper\Url $helperUrl
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magestore\Affiliateplus\Helper\Payment $helperPayment,
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        \Magestore\Affiliateplus\Helper\Url $helperUrl,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $context->getEventDispatcher();
        $this->_helper = $helper;
        $this->_helperConfig = $helperConfig;
        $this->_helperPayment = $helperPayment;
        $this->_helperUrl = $helperUrl;
        $this->_currencyFactory = $currencyFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_messageManager = $messageManager;
        $this->_transportBuilder = $transportBuilder;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }
    /**
     * @param $router
     * @param $params
     * @return string
     */
    public function getUrl($router, $params)
    {
        return $this->_urlBuilder->getUrl($router, $params);
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     */
    protected function sendEmailTemplate($customer, $template, $sender, $templateParams = [], $storeId = null)
    {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($customer->getEmail(), $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
