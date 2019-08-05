<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplus\Model\System\Config\Source\Payment;

class FixedPercentageType
{
    const PAYMENT_FIXED_AMOUNT = 'fixed';
    const PAYMENT_PERCENTAGE_AMOUNT = 'percentage';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PAYMENT_FIXED_AMOUNT,
                'label'=>__('Fixed Amount')
            ],
            [
                'value' => self::PAYMENT_PERCENTAGE_AMOUNT,
                'label'=>__('Percentage')
            ]
        ];
    }
}