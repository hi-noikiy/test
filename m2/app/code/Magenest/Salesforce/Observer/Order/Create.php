<?php
/**
 * Copyright © 2015 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Salesforce extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_Salesforce
 * @author   ThaoPV
 */
namespace Magenest\Salesforce\Observer\Order;

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magenest\Salesforce\Observer\SyncObserver;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\Salesforce\Model\Sync\Order;
use Magenest\Salesforce\Model\Sync\Opportunity;

/**
 * Class Create
 */
class Create extends SyncObserver
{
    protected $pathEnable = 'salesforcecrm/sync/order';
    protected $pathOpportunityEnable = 'salesforcecrm/sync/opportunity';
    protected $pathSyncOption = 'salesforcecrm/sync/order_mode';
    protected $pathSyncOpportunityOption = 'salesforcecrm/sync/opportunity_mode';

    /**
     * @var \Magenest\Salesforce\Model\Sync\Order
     */
    protected $_order;

    /**
     * @var Opportunity
     */
    protected $_opportunity;

    /**
     * Create constructor.
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $config
     * @param Order $order
     * @param Opportunity $opportunity
     */
    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config,
        Order $order,
        Opportunity $opportunity
    ) {
        $this->_order       = $order;
        $this->_opportunity = $opportunity;
        parent::__construct($queueFactory, $config);
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            if ($this->getConfigValue($this->pathEnable)) {
                if ($this->getConfigValue($this->pathSyncOption) == 1) {
                    $this->addToQueue(Queue::TYPE_ORDER, $order->getIncrementId());
                } else {
                    if (!$this->_order->checkExistedOrder($order->getIncrementId())) {
                        $this->_order->sync($order->getIncrementId());
                    }
                }
            }
            if ($this->getConfigValue($this->pathOpportunityEnable)) {
                if ($this->getConfigValue($this->pathSyncOpportunityOption) == 1) {
                    $this->addToQueue(Queue::TYPE_OPPORTUNITY, $order->getIncrementId());
                } else {
                    $this->_opportunity->sync($order->getIncrementId());
                }
            }
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
    }
}
