<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\Sales\Model\Service;

class CreditmemoService
{
    /**
     * @var \Amasty\Affiliate\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Api\ProgramRepositoryInterface
     */
    private $programRepository;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * CreditmemoService constructor.
     * @param \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository,
        \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->scopeConfig = $scopeConfig;
        $this->programRepository = $programRepository;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Service\CreditmemoService $subject
     * @param \Magento\Sales\Model\Order\Creditmemo $result
     * @return mixed
     */
    public function afterRefund($subject, $result)
    {
        if ($this->scopeConfig->getValue('amasty_affiliate/commission/subtract_creditmemo')) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $result->getOrder();

            /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
            $transactions = $this->transactionCollectionFactory->create()
                ->addIncrementIdFilter($order->getIncrementId())
                ->addTypeFilter(\Amasty\Affiliate\Model\Transaction::TYPE_PER_SALE);
            /** @var \Amasty\Affiliate\Model\Transaction $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->getStatus() != $transaction::STATUS_CANCELED) {
                    $fullOrderAmount = $order->getBaseSubtotal() + $order->getBaseDiscountAmount();
                    $currentRefundedAmount = $result->getBaseSubtotal() + $result->getBaseDiscountAmount();
                    $partToSubtract = $currentRefundedAmount / $fullOrderAmount;
                    $totalRefundedAmount = $order->getBaseSubtotalRefunded() + $order->getBaseDiscountRefunded();
                    $refundWithoutCurrent = $totalRefundedAmount - $currentRefundedAmount;
                    $currentOrderAmount = $fullOrderAmount - $refundWithoutCurrent;
                    $fullTransactionCommission = $transaction->getCommission() * $fullOrderAmount / $currentOrderAmount;
                    $subtractAmount = ($fullTransactionCommission * $partToSubtract);

                    $transaction->setCommission($transaction->getCommission() - $subtractAmount);
                    /** @var \Amasty\Affiliate\Model\Account $account */
                    $account = $this->accountRepository->get($transaction->getAffiliateAccountId());

                    if ($transaction->getStatus() == $transaction::STATUS_COMPLETED) {
                        $account->setBalance($account->getBalance() - $subtractAmount);
                        $account->setLifetimeCommission($account->getLifetimeCommission() - $subtractAmount);
                        $this->accountRepository->save($account);
                    }

                    if ($transaction->getStatus() == $transaction::STATUS_ON_HOLD) {
                        /** @var \Amasty\Affiliate\Model\Account $account */
                        $account = $this->accountRepository->get($transaction->getAffiliateAccountId());
                        $account->setOnHold($account->getOnHold() - $subtractAmount);
                        $this->accountRepository->save($account);
                    }

                    $transaction->setBalance($account->getBalance());
                    if ($transaction->getCommission() <= 0) {
                        $transaction->setStatus($transaction::STATUS_CANCELED);
                    }
                    $this->transactionRepository->save($transaction);

                    /** @var \Amasty\Affiliate\Model\Program $program */
                    $program = $this->programRepository->get($transaction->getProgramId());
                    $program->setTotalSales($program->getTotalSales() - $order->getBaseTotalRefunded());
                    $this->programRepository->save($program);
                }
            }
        }

        return $result;
    }
}
