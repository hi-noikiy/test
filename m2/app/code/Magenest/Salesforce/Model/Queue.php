<?php
namespace Magenest\Salesforce\Model;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_ACCOUNT = 'Account';
    const TYPE_CAMPAIGN = 'Campaign';
    const TYPE_CONTACT = 'Contact';
    const TYPE_LEAD = 'Lead';
    const TYPE_ORDER = 'Order';
    const TYPE_PRODUCT = 'Product';
    const TYPE_SUBSCRIBER = 'Subscriber';
    const TYPE_OPPORTUNITY = 'Opportunity';

    protected function _construct()
    {
        $this->_init('Magenest\Salesforce\Model\ResourceModel\Queue');
    }

    public function enqueue($type, $entityId)
    {
        return [
            'type' => $type,
            'entity_id' => $entityId,
            'enqueue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
            'priority' => 1,
        ];
    }

    /**
     * @param $type
     * @return $this->getCollection
     */
    public function getQueueByType($type)
    {
        $queueCollection = $this->getCollection()
            ->addFieldToFilter('type', $type);
        return $queueCollection;
    }

    /**
     * @param $queueArr array
     */
    public function enqueueMultiRecords($queueArr)
    {
        $this->getResource()->getConnection()->insertMultiple(
            $this->getResource()->getMainTable(),
            $queueArr
        );
    }

    public function deleteQueueByType($type)
    {
        $queueCollection = $this->getQueueByType($type);
        $this->deleteMultiQueues($queueCollection->getAllIds());
    }

    public function deleteMultiQueues($collectionArr)
    {
        $collectionSize = count($collectionArr);
        if (!is_array($collectionArr) || $collectionSize == 0) {
            return;
        }
        $resource = $this->getResource();
        $collectionIds = implode(', ', $collectionArr);
        $resource->getConnection()->delete(
            $resource->getMainTable(),
            "{$this->getIdFieldName()} in ({$collectionIds})"
        );
    }
}
