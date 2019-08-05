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

class Parametervalue
{
    const REFER_URL_PARAM_IDENTIFY = '1';
    const REFER_URL_PARAM_AFFILIATE_ID = '2';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::REFER_URL_PARAM_IDENTIFY,
                'label'=>__('Identify Code')
            ],
            [
                'value' => self::REFER_URL_PARAM_AFFILIATE_ID,
                'label'=>__('Affiliate ID')
            ]
        ];
    }
}