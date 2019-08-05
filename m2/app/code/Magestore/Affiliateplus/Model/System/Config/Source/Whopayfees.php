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

class Whopayfees
{
    const RECIPIENT_PAY_FEE = 'recipient';
    const PAYER_PAY_FEE = 'payer';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::RECIPIENT_PAY_FEE,
                'label'=>__('Recipient')
            ],
            [
                'value' => self::PAYER_PAY_FEE,
                'label'=>__('Payer')
            ],
        ];
    }
}