<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

class TimeFormat implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('24h'),
            ],
            [
                'value' => '1',
                'label' => __('12h'),
            ]
        ];
    }
}
