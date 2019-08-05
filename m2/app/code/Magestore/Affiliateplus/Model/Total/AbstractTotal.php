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
namespace Magestore\Affiliateplus\Model\Total;


class AbstractTotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var int
     */
    protected $_hiddentBaseDiscount = 0;
    /**
     * @var int
     */
    protected $_hiddentDiscount = 0;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_helperConfig;
    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_helperAccount;
    /**
     * @var \Magestore\Affiliateplus\Helper\Cookie
     */
    protected $_helperCookie;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_backendQuoteSession;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Block\AbstractTemplate
     */
    protected $_abstractTemplate;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Affiliateplusprogram\Model\Program
     */
    protected $_program;


    /**
     * Affiliateplus constructor.
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     * @param \Magestore\Affiliateplus\Helper\Config $helperConfig
     * @param \Magestore\Affiliateplus\Helper\Account $helperAccount
     * @param \Magestore\Affiliateplus\Helper\Cookie $helperCookie
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Affiliateplus\Block\AbstractTemplate $abstractTemplate
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        \Magestore\Affiliateplus\Helper\Account $helperAccount,
        \Magestore\Affiliateplus\Helper\Cookie $helperCookie,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Affiliateplus\Block\AbstractTemplate $abstractTemplate,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->_helper = $helper;
        $this->_helperConfig = $helperConfig;
        $this->_helperAccount = $helperAccount;
        $this->_helperCookie = $helperCookie;
        $this->_checkoutSession = $checkoutSession;
        $this->_backendQuoteSession = $backendQuoteSession;
        $this->_eventManager = $eventManager;
        $this->_abstractTemplate = $abstractTemplate;
        $this->_objectManager = $objectManager;
        $this->_storeManager =  $storeManager;

    }


    /**
     * @return \Magento\Tax\Model\Calculation
     */
    public function getCaculationTaxModel(){
        return $this->_objectManager->create('Magento\Tax\Model\Calculation');
    }
    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession(){
        return $this->_checkoutSession;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    public  function  getQuoteSession(){
        return $this->_backendQuoteSession;
    }

    /**
     * Get Program by program Id
     * @param $programId
     * @return \Magestore\Affiliateplusprogram\Model\Program
     */
    public function getProgramById($programId){
        if(!$this->_program){
            $this->_program = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\Program')
                    ->load($programId);
        }
        return $this->_program;
    }
}