<?php

namespace ShippyPro\ShippyPro\Model\Config\Source;

class MarkupType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => '%'],
            ['value' => 2, 'label' => __('Fixed Value')],
        ];
    }
}