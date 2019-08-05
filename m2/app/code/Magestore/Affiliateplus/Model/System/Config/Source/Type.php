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

class Type
{
    const XML_PATH_COMMISSION_TYPE_SALES = 'sales';
    const XML_PATH_COMMISSION_TYPE_PROFIT = 'profit';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::XML_PATH_COMMISSION_TYPE_SALES,
                'label'=>__('Value of items sold (Pay per Sale)')
            ],
            [
                'value' => self::XML_PATH_COMMISSION_TYPE_PROFIT,
                'label'=>__('Net profit of sale (Pay per Profit)')
            ]
        ];
    }
}