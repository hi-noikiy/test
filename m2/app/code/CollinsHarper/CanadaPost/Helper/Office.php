<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper;


use \Magento\Framework\App\Helper\Context;
/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Office extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_admin_quote;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkout_session;


    /**
     * @var \CollinsHarper\Core\Logger\Logger
     */
    protected $_chLogged;

    /**
     *
     * @var \CollinsHarper\CanadaPost\Model\ObjectFactory
     */
    private $objectFactory;
    
    /**
     *
     * @var \CollinsHarper\CanadaPost\Helper\DataFactory
     */
    private $helperFactory;


    /**
     * @param Context $context
     * @param \CollinsHarper\Core\Logger\Logger $chLogged
     * @param \Magento\Backend\Model\Session\Quote $adminQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     */
    public function __construct(
        Context $context,
        \CollinsHarper\Core\Logger\Logger $chLogged,
        \Magento\Backend\Model\Session\Quote $adminQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
    )
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_chLogged = $chLogged;
        $this->objectFactory = $objectFactory;
        $this->helperFactory = $helperFactory;
        $this->_admin_quote = $adminQuote;
        $this->_checkout_session = $checkoutSession;
        $this->_regionFactory = $regionFactory;
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_httpHeader = $context->getHttpHeader();
        $this->_eventManager = $context->getEventManager();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_cacheConfig = $context->getCacheConfig();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * 
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkout_session;
    }

    /**
     * 
     * @param Magento\Quote\Model\Quote $quote
     * @param bool $deliver_to_post_office
     * @param int $office_id
     * @throws \Exception
     */
    public function updateShippingAddress($quote, $deliver_to_post_office, $office_id)
    {

        // todo BROKEN
        throw new \Exception(" broken");

        $is_original_address_saved = $this->getCheckoutSession()->getOriginalAddressSaved();

        if ($deliver_to_post_office && !empty($office_id)) {

            $cp_office = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Office')->create()->load($office_id);

            if ($cp_office->getId()) {

                if (empty($is_original_address_saved)) {

                    $this->getCheckoutSession()->setOriginalCity($quote->getShippingAddress()->getCity());

                    $this->getCheckoutSession()->setOriginalAddress($quote->getShippingAddress()->getStreet());

                    $this->getCheckoutSession()->setOriginalPostalCode($quote->getShippingAddress()->getPostcode());

                    $this->getCheckoutSession()->setOriginalProvince($quote->getShippingAddress()->getRegion());

                    $this->getCheckoutSession()->setOfficeId($office_id);

                    $this->getCheckoutSession()->setOriginalAddressSaved(true);

                }

                $region =
                $region = $this->_regionFactory->create()->getCollection()
                    ->addFieldToFilter('code', $cp_office->getProvince())
                    ->addFieldToFilter('country_id', $quote->getShippingAddress()->getCountryId())
                    ->getFirstItem();

                if ($region->getId()) {

                    $region_id = $region->getId();

                } else {

                    $region_id = $cp_office->getProvince();

                }

                $quote->getShippingAddress()
                    ->setCity($cp_office->getCity())
                    ->setStreet($cp_office->getAddress())
                    ->setPostcode($cp_office->getPostalCode())
                    ->setRegionId($region_id)
                    ->save();

            }

        } else if (!empty($is_original_address_saved)){

            $quote->getShippingAddress()
                ->setCity($this->getCheckoutSession()->getOriginalCity())
                ->setStreet($this->getCheckoutSession()->getOriginalAddress())
                ->setPostcode($this->getCheckoutSession()->getOriginalPostalCode())
                ->setRegion($this->getCheckoutSession()->getOriginalProvince())
                ->save();

            $this->getCheckoutSession()->setOriginalAddressSaved(false);

        }

    }

    /**
     * 
     * @param int $office_id
     */
    public function getDetails($office_id)
    {

    }


}