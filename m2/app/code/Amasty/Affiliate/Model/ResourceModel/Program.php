<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;

class Program extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @var Account\CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * Program constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param Account\CollectionFactory $accountCollectionFactory
     * @param \Amasty\Affiliate\Model\CouponFactory $affiliateCouponFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Amasty\Affiliate\Model\CouponFactory $affiliateCouponFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->couponFactory = $affiliateCouponFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_program', 'program_id');
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave(\Magento\Framework\DataObject $object)
    {
        parent::afterSave($object);

        /** @var \Amasty\Affiliate\Model\ResourceModel\Account\Collection $accountCollection */
        $accountCollection = $this->accountCollectionFactory->create();
        /** @var \Amasty\Affiliate\Model\Account $account */
        foreach ($accountCollection as $account) {
            /** @var \Amasty\Affiliate\Model\Coupon $coupon */
            $coupon = $this->couponFactory->create();
            $coupon->addCoupon($object, $account->getAccountId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
