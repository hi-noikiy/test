<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

use Amasty\Affiliate\Model\Transaction as TransactionModel;

class Transaction extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/transaction.phtml';

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $currency;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * Transaction constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param \Amasty\Affiliate\Model\Account $account
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Amasty\Affiliate\Model\Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->currency = $currency;
        $this->customerSession = $customerSession;
        $this->account = $account;
        $this->storeManager = $context->getStoreManager();
        $this->context = $context;
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Balance'));
    }

    /**
     * @return \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection
     */
    public function getTransactions()
    {
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $accountId = $this->accountRepository->getCurrentAccount()->getAccountId();
        if (!$accountId) {
            return false;
        }

        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->collectionFactory->create();
        $transactions->addAccountIdFilter($accountId);
        $transactions->addFrontTypeFilter();
        $transactions->addCompletedFilter();

        return $transactions;
    }

    /**
     * @return $this
     */
    public function getAscTransactions()
    {
        return $this->getTransactions()->addAscSorting();
    }

    public function getDescTransactions()
    {
        return $this->getTransactions()->addDescSorting();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'amasty.affiliate.transaction.pager'
            )->setCollection(
                $this->getTransactions()
            );
            $this->setChild('pager', $pager);
            $this->getTransactions()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param $value
     * @return string
     */
    public function convertToPrice($value)
    {
        $value = $this->account->convertToPrice($value);

        return $value;
    }

    /**
     * @param \Amasty\Affiliate\Model\Transaction $transactions
     * @return \Magento\Framework\Phrase
     */
    public function prepareDetails($transactions)
    {
        $details = __('Per Profit');

        if ($transactions->getType() == $transactions::TYPE_PER_SALE) {
            $details = __('Commission per oder #') . $transactions->getIncrementId();
        } if ($transactions->getType() == $transactions::TYPE_WITHDRAWAL) {
            $details = __('Withdrawal');
        }

        return $details;
    }

    /**
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     * @return string
     */
    public function getPriceClass($transaction)
    {
        $class = 'amasty_affiliate_gain';

        if ($transaction->getCommission() < 0 || $transaction->getType() == $transaction::TYPE_WITHDRAWAL) {
            $class = 'amasty_affiliate_losses';
        }

        return $class;
    }

    /**
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     * @return string
     */
    public function showCharacter($transaction)
    {
        $character = '+';

        if ($transaction->getType() == $transaction::TYPE_WITHDRAWAL) {
            $character = '-';
        }

        return $character;
    }

    /**
     * @return string
     */
    public function getCurrentCurrency()
    {
        return $this->currency->getDefaultCurrency();
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
}
