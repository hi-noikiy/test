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

class Cartproduct
{
    const DISCOUNT_ON_CART = 'cart';
    const DISCOUNT_ON_PRODUCT = 'product';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DISCOUNT_ON_CART,
                'label'=>__('On shopping cart page')
            ],
            [
                'value' => self::DISCOUNT_ON_PRODUCT,
                'label'=>__('On product pages')
            ]
        ];
    }
}