<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class RecurringTerms implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'monthly',
                'label' => __('Monthly')
            ],[
                'value' => 'weekly',
                'label' => __('Weekly')
            ],[
                'value' => 'yearly',
                'label' => __('Yearly')
            ],[
                'value' => 'daily',
                'label' => __('Daily')
            ]
        ];
    }
}