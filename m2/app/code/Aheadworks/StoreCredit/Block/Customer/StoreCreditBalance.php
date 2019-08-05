<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Customer;

use Aheadworks\StoreCredit\Model\Config;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance
 */
class StoreCreditBalance extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CustomerStoreCreditManagementInterface
     */
    protected $customerStoreCreditService;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param Context $context
     * @param Config $config
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param CurrentCustomer $currentCustomer
     * @param PriceHelper $priceHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        CurrentCustomer $currentCustomer,
        PriceHelper $priceHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->config = $config;
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->currentCustomer = $currentCustomer;
        $this->priceHelper = $priceHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * Get customer balance
     *
     * @return string
     */
    public function getCustomerStoreCreditBalanceFormatted()
    {
        return $this->priceHelper->currency(
            $this->getCustomerStoreCreditBalance(),
            true,
            false
        );
    }

    /**
     * Get customer balance
     *
     * @return float
     */
    public function getCustomerStoreCreditBalance()
    {
        return $this->customerStoreCreditService
            ->getCustomerStoreCreditBalance($this->currentCustomer->getCustomerId());
    }
}
