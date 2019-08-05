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

namespace Magestore\Affiliateplus\Model\System\Config\Source;

class Fixedpercentage
{
    const COMMISSION_FIXED_AMOUNT_PER_ITEM = 'fixed';
    const COMMISSION_PERCENTAGE = 'percentage';
    const COMMISSION_FIXED_AMOUNT_PER_CART = 'cart_fixed';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::COMMISSION_FIXED_AMOUNT_PER_ITEM,
                'label'=>__('Fixed Amount')
            ],
            [
                'value' => self::COMMISSION_PERCENTAGE,
                'label'=>__('Percentage')
            ],
            [
                'value' => self::COMMISSION_FIXED_AMOUNT_PER_CART,
                'label'=>__('Fixed amount for whole cart (Commission On Cart mode)')
            ]
        ];
    }
}