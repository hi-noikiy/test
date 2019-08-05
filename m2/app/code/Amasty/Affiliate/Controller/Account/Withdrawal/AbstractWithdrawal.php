<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Account\Withdrawal;

use Magento\Framework\App\Action\Context;
use Amasty\Affiliate\Model\Withdrawal;

abstract class AbstractWithdrawal extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Withdrawal
     */
    protected $withdrawal;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    protected $account;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
     */
    protected $withdrawalRepository;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection
     */
    protected $withdrawalCollectionFactory;
    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    protected $url;

    public function __construct(
        Context $context,
        Withdrawal $withdrawal,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\WithdrawalRepositoryInterface $withdrawalRepository,
        \Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory $withdrawalCollectionFactory,
        \Amasty\Affiliate\Model\Url $url
    ) {
        $this->withdrawal = $withdrawal;
        $this->account = $account;
        $this->accountRepository = $accountRepository;
        $this->scopeConfig = $scopeConfig;
        $this->withdrawalRepository = $withdrawalRepository;
        $this->withdrawalCollectionFactory = $withdrawalCollectionFactory;
        $this->url = $url;
        parent::__construct($context);
    }

    protected function validateWithdrawal($requestedAmount)
    {
        /** @var \Amasty\Affiliate\Model\Account $currentAccount */
        $currentAccount = $this->accountRepository->getCurrentAccount();
        $availableAmount = $currentAccount->getAvailable();

        /** @var \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection $withdrawalCollection */
        $withdrawalCollection = $this->withdrawalCollectionFactory->create();
        $pending = $withdrawalCollection->getCurrentAccountPendingAmount();
        $availableAmount = $availableAmount - $pending;

        $minimumAvailable = $this->scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_balance');
        $minimumRequest = $this->scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_amount');

        if ($requestedAmount <= 0) {
            $this->messageManager->addErrorMessage(__('Please enter an amount more than zero'));
            return false;
        }

        if ($requestedAmount >= $availableAmount
            || $availableAmount <= $minimumAvailable
        ) {
            $this->messageManager->addErrorMessage(__('You have no enough funds available for the withdrawal'));
            return false;
        }

        if ($requestedAmount < $minimumRequest) {
            $minimumPayout = $currentAccount->convertToPrice($minimumRequest);
            $this->messageManager->addErrorMessage(
                __("The minimum payout amount is $minimumPayout. Please enter an amount more than $minimumPayout")
            );
            return false;
        }

        return true;
    }
}
