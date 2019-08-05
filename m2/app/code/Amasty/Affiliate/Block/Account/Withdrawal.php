<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

use Magento\Framework\View\Element\Template;

class Withdrawal extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/withdrawal.phtml';

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    private $urlBuilder;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(
        Template\Context $context,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory $collectionFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\Url $urlBuilder,
        array $data = []
    ) {
        $this->account = $account;
        $this->collectionFactory = $collectionFactory;
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function showCancel($status)
    {
        $showCancel = false;

        $allowedStatuses = [
            \Amasty\Affiliate\Model\Transaction::STATUS_PENDING
        ];

        if (in_array($status, $allowedStatuses)) {
            $showCancel = true;
        }

        return $showCancel;
    }

    /**
     * @return \Amasty\Affiliate\Model\Account
     */
    public function getAccount()
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getCurrentAccount();
        $account->preparePrices();

        return $account;
    }

    /**
     * @return mixed
     */
    public function getMinimumAmount()
    {
        $amount = $this->_scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_amount');

        return $amount;
    }

    /**
     * @return mixed|string
     */
    public function getMinimumPriceAmount()
    {
        $amount = $this->getMinimumAmount();
        $amount = $this->account->convertToPrice($amount);

        return $amount;
    }

    public function getMinimumBalance()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_balance');
    }

    public function getMinimumBalancePrice()
    {
        return $this->account->convertToPrice($this->getMinimumBalance());
    }

    /**
     * @return \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection|bool
     */
    public function getWithdrawals()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection $collection */
        $collection = $this->collectionFactory->create();
        $accountId = $this->getAccount()->getAccountId();
        if (!$accountId) {
            return false;
        }

        $collection->addAccountIdFilter($accountId);

        return $collection;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param \Amasty\Affiliate\Model\Withdrawal $withdrawal
     * @return string
     */
    public function getCancelUrl($withdrawal)
    {
        $id = $withdrawal->getTransactionId();
        $url = $this->urlBuilder->getUrl(
            $this->urlBuilder->getPath('account_withdrawal/cancel'),
            ['withdrawal_id' => $id]
        );

        return $url;
    }

    /**
     * @param \Amasty\Affiliate\Model\Withdrawal $withdrawal
     * @return string
     */
    public function getRepeatUrl($withdrawal)
    {
        $id = $withdrawal->getTransactionId();
        $url = $this->urlBuilder->getUrl(
            $this->urlBuilder->getPath('account_withdrawal/repeat'),
            ['withdrawal_id' => $id]
        );

        return $url;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWithdrawals()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'amasty.affiliate.withdrawal.pager'
            )->setCollection(
                $this->getWithdrawals()
            );
            $this->setChild('pager', $pager);
            $this->getWithdrawals()->load();
        }
        return $this;
    }
}
