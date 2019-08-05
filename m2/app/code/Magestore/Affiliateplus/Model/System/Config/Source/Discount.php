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

class Discount
{
    const ONLY_AFFILIATE_PROGRAM_DISCOUNT = 'affiliate';
    const ONLY_SHOPPING_CART_DISCOUNT = 'system';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label'=>__('Both Affiliate program Discount and Shopping cart Discount')
            ],
            [
                'value' => self::ONLY_AFFILIATE_PROGRAM_DISCOUNT,
                'label'=>__('Only Affiliate program Discount')
            ],
            [
                'value' => self::ONLY_SHOPPING_CART_DISCOUNT,
                'label' =>__('Only Shopping cart Discount')
            ],
        ];
    }
}