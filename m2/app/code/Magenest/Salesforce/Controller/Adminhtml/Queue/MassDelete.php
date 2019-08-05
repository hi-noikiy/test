<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\Salesforce\Model\ResourceModel\Queue\CollectionFactory;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            /** @var \Magenest\Salesforce\Model\Queue $queueModel */
            $queueModel = $this->queueFactory->create();
            $collectionArr = [];
            $maxRecord = 20;
            $lastItemId = $collection->getLastItem()->getId();
            $count = 0;
            foreach ($collection as $item) {
                $count++;
                $collectionArr[] = $item->getId();
                if ($count >= $maxRecord || $item->getId() == $lastItemId) {
                    $queueModel->deleteMultiQueues($collectionArr);
                    $collectionArr = [];
                    $count=0;
                }
            }
            $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::queue');
    }
}
