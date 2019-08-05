<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

class RadiusType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'select',
                'label' => __('Dropdown'),
            ],
            [
                'value' => 'range',
                'label' => __('Slider'),
            ]
        ];
    }
}
