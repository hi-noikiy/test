<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Customer;

use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;
use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Account\Transaction;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\StoreCredit\Model\Config;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class Subscribe
 *
 * @package Aheadworks\StoreCredit\Block\Customer
 */
class Subscribe extends StoreCreditBalance
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_StoreCredit::customer/newsletter/subscribe.phtml';

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param Context $context
     * @param Config $config
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param CurrentCustomer $currentCustomer
     * @param PriceHelper $priceHelper
     * @param Transaction $transaction
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        CurrentCustomer $currentCustomer,
        PriceHelper $priceHelper,
        Transaction $transaction,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->transaction = $transaction;
        parent::__construct(
            $context,
            $config,
            $customerStoreCreditService,
            $currentCustomer,
            $priceHelper,
            $httpContext,
            $data
        );
    }

    /**
     * Is subscribed customer or not
     *
     * @return bool
     */
    public function isSubscribed()
    {
        $balanceUpdateNotificationStatus = $this->customerStoreCreditService
            ->getCustomerBalanceUpdateNotificationStatus($this->currentCustomer->getCustomerId());
        return $balanceUpdateNotificationStatus == SubscribeStatus::SUBSCRIBED ? true : false;
    }

    /**
     * Show block or not
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->transaction->getTransactions() && count($this->transaction->getTransactions()->getItems());
    }
}
