<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Paysafe Card Type Dropdown source
 */
class CardType implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'Visa',
                'label' => __('Visa')
            ],
            [
                'value' => 'MasterCard',
                'label' => __('MasterCard')
            ],
            [
                'value' => 'Maestro',
                'label' => __('Maestro')
            ],
            [
                'value' => 'AmericanExpress',
                'label' => __('American Express')
            ],
            [
                'value' => 'Diners',
                'label' => __('Diners')
            ],
            [
                'value' => 'JCB',
                'label' => __('JCB')
            ]
        ];
    }
}
