<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Transaction constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connectionName = null
    ) {
        $this->order = $order;
        $this->date = $date;
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_transaction', 'transaction_id');
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        /** @var \Amasty\Affiliate\Model\Transaction $loadedObject */
        $loadedObject = $this->entityManager->load($object, $value);

        return $loadedObject;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->gmtTimestamp());
        return parent::save($object);
    }
}
