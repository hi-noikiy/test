<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\Transaction;

use Amasty\Affiliate\Model\ResourceModel\Transaction\Collection;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Model\Transaction;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\Store;

/**
 * Class DataProvider
 * @package Amasty\Affiliate\Model\Transaction
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    private $statusCollectionFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param UrlInterface $urlBuilder
     * @param CurrencyInterface $currency
     * @param StoreManagerInterface $storeManager
     * @param ContextInterface $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        UrlInterface $urlBuilder,
        CurrencyInterface $currency,
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->statusCollectionFactory = $statusCollectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Transaction $transaction */
        foreach ($items as $transaction) {
            $this->loadedData[$transaction->getId()] = $transaction->getData();
            $this->loadedData[$transaction->getId()]['affiliate_url'] = $this->urlBuilder->getUrl(
                'amasty_affiliate/account/edit',
                ['id' => $transaction->getAffiliateAccountId()]
            );

            if (!$transaction->getCustomerAccountId()) {
                $this->loadedData[$transaction->getId()]['customer_link_class'] = 'inactiveLink';
            }
            $this->loadedData[$transaction->getId()]['customer_url'] = $this->urlBuilder->getUrl(
                'customer/index/edit',
                ['id' => $transaction->getCustomerAccountId()]
            );
            $this->loadedData[$transaction->getId()]['order_url'] = $this->urlBuilder->getUrl(
                'sales/order/view',
                ['order_id' => $transaction->getOrderId()]
            );

            $this->preparePrices($transaction->getTransactionId());

            $statuses = $transaction->getAvailableStatuses();
            $this->loadedData[$transaction->getId()]['status'] = $statuses[$transaction->getStatus()];
            $types = $transaction->getAvailableTypes();
            $this->loadedData[$transaction->getId()]['type'] = $types[$transaction->getType()];
        }

        return $this->loadedData;
    }

    /**
     * Add currency to price and format it
     * @param $transactionId
     */
    protected function preparePrices($transactionId)
    {
        $fieldsToPrice = [
            'commission',
            'balance',
            'discount',
            'base_grand_total',
            'base_subtotal'
        ];

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', Store::DEFAULT_STORE_ID)
        );

        $currency = $this->currency->getCurrency($store->getBaseCurrencyCode());

        foreach ($fieldsToPrice as $field) {
            $this->loadedData[$transactionId][$field] =
                $currency->toCurrency(sprintf("%f", $this->loadedData[$transactionId][$field]));
        }
    }
}
