<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\Sales\Model;

use \Magento\Sales\Model\Order as OrderModel;

class Order
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory
     */
    private $programsCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Amasty\Affiliate\Api\ProgramRepositoryInterface
     */
    private $programRepository;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon
     */
    private $coupon;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection
     */
    private $couponCollection;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $programsCollectionFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository
     * @param \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon $coupon
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $programsCollectionFactory,
        \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository,
        \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository,
        \Amasty\Affiliate\Model\ResourceModel\Coupon $coupon,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->programsCollectionFactory = $programsCollectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->programRepository = $programRepository;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param \Magento\Sales\Model\Order $result
     * @return mixed
     */
    public function afterSetStatus($subject, $result)
    {
        $orderStatus = $result->getStatus();
        $addStatus = $this->scopeConfig->getValue('amasty_affiliate/commission/add_commission_status');

        $subtractStatuses = explode(
            ',',
            $this->scopeConfig->getValue('amasty_affiliate/commission/subtract_commission_status')
        );
        if (in_array($orderStatus, $subtractStatuses)) {
            /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
            $transactions = $this->transactionCollectionFactory->create()
                ->addIncrementIdFilter($result->getIncrementId())
                ->addTypeFilter(\Amasty\Affiliate\Model\Transaction::TYPE_PER_SALE);

            /** @var \Amasty\Affiliate\Model\Transaction $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->getType() == $transaction::TYPE_FOR_FUTURE_PER_PROFIT) {
                    $transaction = $transaction->getPerProfitTransaction($transaction);
                }

                if ($transaction->getStatus() == $transaction::STATUS_COMPLETED) {
                    /** @var \Amasty\Affiliate\Model\Account $account */
                    $account = $this->accountRepository->get($transaction->getAffiliateAccountId());
                    $account->setBalance($account->getBalance() - $transaction->getCommission());
                    $account->setLifetimeCommission($account->getLifetimeCommission() - $transaction->getCommission());
                    $this->accountRepository->save($account);
                }

                if ($transaction->getStatus() == $transaction::STATUS_ON_HOLD) {
                    /** @var \Amasty\Affiliate\Model\Account $account */
                    $account = $this->accountRepository->get($transaction->getAffiliateAccountId());
                    $account->setOnHold($account->getOnHold() - $transaction->getCommission());
                    $this->accountRepository->save($account);
                }

                $transaction->setCommission(0);
                $transaction->setStatus($transaction::STATUS_CANCELED);
                $this->transactionRepository->save($transaction);
            }
        }

        /** @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programs */
        $programs = $this->programsCollectionFactory->create()->getProgramsByRuleIds($result->getAppliedRuleIds());

        $couponCode = $result->getCouponCode();
        if ($couponCode
            && $this->couponCollection->isAffiliateCoupon($couponCode)
            && $this->coupon->getProgramId($couponCode)
        ) {
            $programs->addProgramIdFilter($this->coupon->getProgramId($couponCode));
        }

        /** @var \Amasty\Affiliate\Model\Program $program */
        foreach ($programs as $program) {
            if ($orderStatus == $addStatus && $result->getBaseSubtotalRefunded() == 0) {
                $program->addTransaction($result, \Amasty\Affiliate\Model\Transaction::STATUS_COMPLETED);
            }
            if ($result->getState()== OrderModel::STATE_COMPLETE) {
                $program->setTotalSales($program->getTotalSales() + $result->getBaseSubtotal());
                $this->programRepository->save($program);
            }
        }
        return $result;
    }
}
