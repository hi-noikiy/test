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

class Period
{
    const PAYMENT_RUCURRING_PERIOD_WEEKLY = '7';
    const PAYMENT_RUCURRING_PERIOD_MONTHLY = '30';
    const PAYMENT_RUCURRING_PERIOD_YEARLY = '365';
    const PAYMENT_RUCURRING_PERIOD_CUSTOM = '0';

    /**
     * @return array
     */
    public function toOptionArray(){
        return [
            [
                'value' => self::PAYMENT_RUCURRING_PERIOD_WEEKLY,
                'label' => __('Weekly')
            ],
            [
                'value' => self::PAYMENT_RUCURRING_PERIOD_MONTHLY,
                'label' => __('Monthly')
            ],
            [
                'value' => self::PAYMENT_RUCURRING_PERIOD_YEARLY,
                'label' => __('Yearly')
            ],
            [
                'value' => self::PAYMENT_RUCURRING_PERIOD_CUSTOM,
                'label' => __('Custom Period')
            ],
        ];
    }
}