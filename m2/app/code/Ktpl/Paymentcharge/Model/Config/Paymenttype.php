<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Paymentcharge\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
class Paymenttype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Percentage')], ['value' => 2, 'label' => __('Fixed')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [2 => __('Fixed'), 1 => __('Percentage')];
    }
}
