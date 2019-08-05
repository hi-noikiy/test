<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon
     */
    private $coupon;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection
     */
    private $couponCollection;

    /**
     * SalesOrderAfterPlaceObserver constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon $coupon
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Amasty\Affiliate\Model\ResourceModel\Coupon $coupon,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->cartRepository = $cartRepository;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($order->getQuoteId());

        /** @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programs */
        $programs = $this->collectionFactory->create()->getProgramsByRuleIds($quote->getAppliedRuleIds());
        $programs->addActiveFilter();
        $couponCode = $order->getCouponCode();
        if ($couponCode
            && $this->couponCollection->isAffiliateCoupon($couponCode)
            && $this->coupon->getProgramId($couponCode)
        ) {
            $programs->addProgramIdFilter($this->coupon->getProgramId($couponCode));
        }

        /** @var \Amasty\Affiliate\Model\Program $program */
        foreach ($programs as $program) {
            $program->addTransaction($order);
        }

        return $this;
    }
}
