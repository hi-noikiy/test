<?php
namespace Krish\CriticReview\Model\ResourceModel\Review;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'review_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Krish\CriticReview\Model\Review', 'Krish\CriticReview\Model\ResourceModel\Review');
    }
}
