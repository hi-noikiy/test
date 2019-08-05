<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Queue;

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Product
 * @package Magenest\Salesforce\Controller\Adminhtml\Queue
 */
class Product extends \Magento\Backend\App\Action
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
    protected $type = Queue::TYPE_PRODUCT;

    /**
     * Order constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Customer\Model\Customer $customerCollection */
            $productCollection = $this->productFactory->create()->getCollection();
            /** @var \Magenest\Salesforce\Model\Queue $queue */
            $queue = $this->queueFactory->create();
            try {
                $queue->deleteQueueByType($this->type);
                $productCollectionArr = [];
                $lastItemId = $productCollection->getLastItem()->getId();
                $maxRecord = 5000;
                $count = 0;
                foreach ($productCollection as $product) {
                    $count++;
                    $productCollectionArr[] = $queue->enqueue($this->type, $product->getId());
                    if ($count >= $maxRecord || $product->getId() == $lastItemId) {
                        $queue->enqueueMultiRecords($productCollectionArr);
                        $productCollectionArr = [];
                        $count = 0;
                    }
                }
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('All Products have been added to queue.'),
                ]));
                return;
            } catch (\Exception $e) {
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('Something went wrong while adding record(s) to queue. Error: '.$e->getMessage())
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
