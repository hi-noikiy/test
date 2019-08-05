<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ReviewStatuses implements ArrayInterface
{
    const PENDING = 0;

    const APPROVED = 1;

    const DECLINED = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::PENDING,
                'label' => __('Pending'),
            ],
            [
                'value' => self::APPROVED,
                'label' => __('Approved'),
            ],
            [
                'value' => self::DECLINED,
                'label' => __('Declined'),
            ],
        ];
    }
}
