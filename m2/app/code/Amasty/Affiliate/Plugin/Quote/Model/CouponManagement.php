<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Plugin\Quote\Model;

class CouponManagement
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * CouponManagement constructor.
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $subject
     * @param $cartId
     * @param $couponCode
     */
    public function beforeSet(\Magento\Quote\Model\CouponManagement $subject, $cartId, $couponCode)
    {
        $this->registry->register(\Amasty\Affiliate\Model\RegistryConstants::CURRENT_COUPON_CODE, $couponCode);
    }
}
