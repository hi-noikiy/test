<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Queue;

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class Campaign
 * @package Magenest\Salesforce\Controller\Adminhtml\Queue
 */
class Campaign extends \Magento\Backend\App\Action
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var string
     */
    protected $type = Queue::TYPE_CAMPAIGN;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * Order constructor.
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->_ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $ruleCollection = $this->_ruleFactory->create()->getCollection();
            /** @var \Magenest\Salesforce\Model\Queue $queue */
            $queue = $this->queueFactory->create();
            try {
                $queue->deleteQueueByType($this->type);
                $ruleCollectionArr = [];
                $lastItemId = $ruleCollection->getLastItem()->getId();
                $maxRecord = 5000;
                $count = 0;
                /** @var \Magento\CatalogRule\Model\Rule $rule */
                foreach ($ruleCollection as $rule) {
                    $count++;
                    $ruleCollectionArr[] = $queue->enqueue($this->type, $rule->getId());
                    if ($count >= $maxRecord || $rule->getId() == $lastItemId) {
                        $queue->enqueueMultiRecords($ruleCollectionArr);
                        $ruleCollectionArr = [];
                        $count = 0;
                    }
                }
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('All Campaigns have been added to queue.'),
                ]));
                return;
            } catch (\Exception $e) {
                $this->getResponse()->setBody(json_encode([
                    'error' => 1,
                    'message' => __('Something went wrong while adding all records to queue. Error: '.$e->getMessage())
                ]));
                return;
            }
        } else {
            return $this->_redirect('*/*/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::config_salesforce');
    }
}
