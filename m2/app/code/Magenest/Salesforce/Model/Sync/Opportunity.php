<?php

namespace Magenest\Salesforce\Model\Sync;

use Magenest\Salesforce\Model\Connector;
use Magenest\Salesforce\Model\Data;
use Magenest\Salesforce\Model\QueueFactory;
use Magenest\Salesforce\Model\ReportFactory as ReportFactory;
use Magenest\Salesforce\Model\RequestLogFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\OrderFactory;

class Opportunity extends Connector
{
    const SALESFORCE_OPPORTUNITY_ATTRIBUTE_CODE = 'salesforce_opportunity_id';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magenest\Salesforce\Model\Sync\Account
     */
    protected $_account;

    /**
     * @var \Magenest\Salesforce\Model\Sync\Contact
     */
    protected $_contact;

    /**
     * @var \Magenest\Salesforce\Model\Sync\Product
     */
    protected $_product;

    protected $_order;
    /**
     * @var Job
     */
    protected $_job;

    protected $_data;

    protected $existedOrders = null;

    protected $createOpportunityIds = null;

    protected $updateOpportunityIds = null;

    protected $dataGetter;


    /**
     * Order constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param OrderFactory $orderFactory
     * @param Order $order
     * @param Account $account
     * @param Contact $contact
     * @param Product $product
     * @param Job $job
     * @param DataGetter $dataGetter
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        Data $data,
        OrderFactory $orderFactory,
        Order $order,
        Account $account,
        Contact $contact,
        Product $product,
        Job $job,
        DataGetter $dataGetter,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory
    )
    {
        parent::__construct($scopeConfig, $resourceConfig, $reportFactory, $queueFactory, $requestLogFactory);
        $this->_orderFactory = $orderFactory;
        $this->_order = $order;
        $this->_account = $account;
        $this->_contact = $contact;
        $this->_product = $product;
        $this->_data = $data;
        $this->_type = 'Opportunity';
        $this->_table = 'opportunity';
        $this->_job = $job;
        $this->dataGetter = $dataGetter;
    }

    /**
     * @param string $increment_id
     * @return mixed|string
     * @throws \Exception
     */
    public function sync($increment_id)
    {
        /** @var OrderModel $order */
        $order = $this->_orderFactory->create()->loadByIncrementId($increment_id);
        $date = date('Y-m-d', strtotime($order->getCreatedAt()));

        $params = $this->_data->getOpportunity($order, $this->_type);

        $params +=
            [
                'CloseDate' => $date,
                'Name' => $order->getIncrementId(),
                'StageName' => 'Prospecting',
            ];
        $existed = $this->checkExistedOpportunity($order);
        if (!$existed) {
            $opportunityId = $this->createRecords($this->_type, $params, $order->getIncrementId());
            $this->saveAttribute($order, $opportunityId);
            /**
             * Sync OpportunityLineItem
             */

            $params = [];
            $itemIds = [];
            $opportunityId = $order->getData(self::SALESFORCE_OPPORTUNITY_ATTRIBUTE_CODE);
            foreach ($order->getAllItems() as $item) {
                $qty = $item->getQtyOrdered();
                $price = $item->getPrice() - $item->getDiscountAmount() / $qty;
                $pricebookEntryId = $item->getProduct()->getData(Product::SALESFORCE_PRICEBOOKENTRY_ATTRIBUTE_CODE);

                if ($price > 0) {
                    $productId = $item->getProduct()->getData(Product::SALESFORCE_PRODUCT_ATTRIBUTE_CODE);
                    if (!$productId) {
                        $productId = $this->_product->sync($item->getProductId());
                    }
                    if ($productId && $opportunityId) {
                        if (!$pricebookEntryId) {
                            $pricebookEntryId = $this->searchRecords('PricebookEntry', 'Product2Id', $productId);
                        }
                        $info = [
                            'PricebookEntryId' => $pricebookEntryId,
                            'OpportunityId' => $opportunityId,
                            'Quantity' => $qty,
                            'UnitPrice' => $price,
                        ];
                        $params[] = $info;
                        $itemIds[] = ['mid' => $item->getProductId()];
                    }
                }
            }
            if ($taxInfo = $this->_order->getTaxItemInfo($order, $opportunityId)) {
                $taxInfo['OpportunityId'] = $opportunityId;
                unset($taxInfo['OrderId']);
                $params[] = $taxInfo;
                $itemIds[] = ['mid' => 'TAX'];
            }
            if ($shippingInfo = $this->_order->getShippingItemInfo($order, $opportunityId)) {
                $shippingInfo['OpportunityId'] = $opportunityId;
                unset($shippingInfo['OrderId']);
                $params[] = $shippingInfo;
                $itemIds[] = ['mid' => 'SHIPPING'];
            }
            $response = $this->_job->sendBatchRequest('insert', 'OpportunityLineItem', json_encode($params));
            $this->saveReports('create', 'OpportunityLineItem', $response, $itemIds);

        } else {
            $opportunityId = $existed['sid'];
            $this->updateRecords($this->_type, $opportunityId, $params, $order->getIncrementId());
        }


        return $opportunityId;
    }


    public function syncAllOpportunities()
    {
        try {
            $orders = $this->_orderFactory->create()->getCollection();
            $lastOrderId = $orders->getLastItem()->getId();
            $count = 0;
            $response = [];
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($orders as $order) {
                $this->addRecord($order->getIncrementId());
                $count++;
                if ($count >= 10000 || $order->getId() == $lastOrderId) {
                    $response += $this->syncQueue();
                }
            }
            return $response;
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
        return null;
    }

    /**
     * @param string $orderIncrementId
     */
    public function addRecord($orderIncrementId)
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        $id = $this->checkExistedOpportunity($order);
        if ($order->getIncrementId() && !$id) {
            $this->addToCreateOpportunityQueue($order);
        } else {
            $this->addToUpdateOpportunityQueue($id['mObj'], $id['sid']);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array|bool
     */
    public function checkExistedOpportunity($order)
    {
        $existedOrders = $this->getAllSalesforceOpportunity();
        $orderIncrementId = $order->getIncrementId();
        if (isset($existedOrders[$orderIncrementId])) {
            unset($this->existedOrders[$orderIncrementId]);
            return [
                'mObj' => $order,
                'sid' => $existedOrders[$orderIncrementId]['Id']
            ];
        }
        return false;
    }

    public function getAllSalesforceOpportunity()
    {
        if (!is_null($this->existedOrders)) {
            return $this->existedOrders;
        }
        $existedOrders = $this->dataGetter->getAllSalesforceOpportunities();
        $allOrders = [];
        foreach ($existedOrders as $key => $value) {
            $allOrders[$value['Name']] = $value;
        }
        $this->existedOrders = $allOrders;
        return $this->existedOrders;
    }


    public function syncQueue()
    {
        $createOpportunityResponse = $this->createOpportunities();
        $this->saveAttributes($this->createOpportunityIds, $createOpportunityResponse);
        $createOpportunityLineItemResponse = $this->createOpportunityLineItem();
        $updateOpportunityResponse = $this->updateOpportunities();
        $this->saveAttributes($this->updateOpportunityIds, $updateOpportunityResponse);
        $response = $createOpportunityResponse + $createOpportunityLineItemResponse + $updateOpportunityResponse;

        $this->unsetCreateOpportunityQueue();
        $this->unsetUpdateOpportunityQueue();
        return $response;
    }


    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function addToCreateOpportunityQueue($order)
    {
        $this->createOpportunityIds[] = [
            'mid' => $order->getIncrementId(),
            'mObj' => $order
        ];
    }

    protected function unsetCreateOpportunityQueue()
    {
        $this->createOpportunityIds = null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $salesforceId
     */
    protected function addToUpdateOpportunityQueue($order, $salesforceId)
    {
        $this->updateOpportunityIds[] = [
            'mObj' => $order,
            'mid' => $order->getIncrementId(),
            'sid' => $salesforceId
        ];
    }

    protected function unsetUpdateOpportunityQueue()
    {
        $this->updateOpportunityIds = null;
    }

    protected function createOpportunities()
    {
        $response = [];
        if (!is_null($this->createOpportunityIds)) {
            $response = $this->sendOpportunitiesRequest($this->createOpportunityIds, 'insert');
        }
        return $response;
    }

    protected function updateOpportunities()
    {
        $response = [];
        if (!is_null($this->updateOpportunityIds)) {
            $response = $this->sendOpportunitiesRequest($this->updateOpportunityIds, 'update');
        }
        return $response;
    }

    protected function createOpportunityLineItem()
    {
        if (is_null($this->createOpportunityIds)) {
            return [];
        }
        $params = [];
        $itemIds = [];
        $orderIds = $this->createOpportunityIds;

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orderIds as $orderId) {
            $order = $orderId['mObj'];
            $opportunityId = $order->getData(self::SALESFORCE_OPPORTUNITY_ATTRIBUTE_CODE);
            foreach ($order->getAllItems() as $item) {
                $qty = $item->getQtyOrdered();
                $price = $item->getPrice() - $item->getDiscountAmount() / $qty;
                $pricebookEntryId = $item->getProduct()->getData(Product::SALESFORCE_PRICEBOOKENTRY_ATTRIBUTE_CODE);

                if ($price > 0) {
                    $productId = $item->getProduct()->getData(Product::SALESFORCE_PRODUCT_ATTRIBUTE_CODE);
                    if (!$productId) {
                        $productId = $this->_product->sync($item->getProductId());
                    }
                    if ($productId && $opportunityId) {
                        if (!$pricebookEntryId) {
                            $pricebookEntryId = $this->searchRecords('PricebookEntry', 'Product2Id', $productId);
                        }
                        $info = [
                            'PricebookEntryId' => $pricebookEntryId,
                            'OpportunityId' => $opportunityId,
                            'Quantity' => $qty,
                            'UnitPrice' => $price,
                        ];
                        $params[] = $info;
                        $itemIds[] = ['mid' => $item->getProductId()];
                    }
                }
            }
            if ($taxInfo = $this->_order->getTaxItemInfo($order, $opportunityId)) {
                $taxInfo['OpportunityId'] = $opportunityId;
                unset($taxInfo['OrderId']);
                $params[] = $taxInfo;
                $itemIds[] = ['mid' => 'TAX'];
            }
            if ($shippingInfo = $this->_order->getShippingItemInfo($order, $opportunityId)) {
                $shippingInfo['OpportunityId'] = $opportunityId;
                unset($shippingInfo['OrderId']);
                $params[] = $shippingInfo;
                $itemIds[] = ['mid' => 'SHIPPING'];
            }
        }
        $response = $this->_job->sendBatchRequest('insert', 'OpportunityLineItem', json_encode($params));
        $this->saveReports('create', 'OpportunityLineItem', $response, $itemIds);
        return $response;
    }

    protected function sendOpportunitiesRequest($opportunityIds, $operation)
    {
        $params = [];
        foreach ($opportunityIds as $orderId) {
//            $order = $this->_orderFactory->create()->loadByIncrementId($orderId['mid']);
            $order = $orderId['mObj'];
            $date = date('Y-m-d', strtotime($order->getCreatedAt()));
            $info = $this->_data->getOpportunity($order, $this->_type);
            $info +=
                [
                    'CloseDate' => $date,
                    'Name' => $order->getIncrementId(),
                    'StageName' => 'Prospecting',
                ];
            if (isset($orderId['sid'])) {
                $info += ['Id' => $orderId['sid']];
            }
            $params[] = $info;
        }
        $response = $this->_job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $opportunityIds);
        return $response;
    }

    /**
     * @param $orderIds
     * @param $response
     * @throws \Exception
     */
    protected function saveAttributes($orderIds, $response)
    {
        if (empty($orderIds) || is_null($orderIds))
            return;
        if (is_array($response) && is_array($orderIds)) {
            $total = count($response);
            for ($i = 0; $i < $total; $i++) {
//                $order = $this->_orderFactory->create()->loadByIncrementId($orderIds[$i]['mid']);
                $order = $orderIds[$i]['mObj'];
                if (isset($response[$i]['id']) && $order->getId()) {
                    $this->saveAttribute($order, $response[$i]['id']);
                }
            }
        } else {
            throw new \Exception('Response not an array');
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $salesforceId
     */
    protected function saveAttribute($order, $salesforceId)
    {
        $resource = $order->getResource();
        $order->setData(self::SALESFORCE_OPPORTUNITY_ATTRIBUTE_CODE, $salesforceId);
        $resource->saveAttribute($order, self::SALESFORCE_OPPORTUNITY_ATTRIBUTE_CODE);
    }
}
