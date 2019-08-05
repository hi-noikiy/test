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
class Transaction implements \Magento\Framework\Option\ArrayInterface
{
    const AUTH_VALUE = 'authorize';
    const CAPT_VALUE = 'authorize_capture';
    const AUTH = 'auth';
    const CAPT = 'purchase';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'authorize', 'label' => __('Authorize Only')], ['value' => 'authorize_capture', 'label' => __('Authorize and Capture')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['authorize' => __('Authorize Only'), 'authorize_capture' => __('Authorize and Capture')];
    }
}
