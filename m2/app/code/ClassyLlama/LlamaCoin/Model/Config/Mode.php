<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ClassyLlama\LlamaCoin\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'development', 'label' => __('Development')], ['value' => 'production', 'label' => __('production')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['development' => __('Development'), 'production' => __('Production')];
    }
}
