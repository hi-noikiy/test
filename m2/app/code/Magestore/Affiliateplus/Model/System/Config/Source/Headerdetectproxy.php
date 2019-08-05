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

class Headerdetectproxy
{
    /**
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => 1,
                'label' => ('HTTP_VIA')
            ],
            [
                'value' => 2,
                'label' => ('HTTP_X_FORWARDED_FOR')
            ],
            [
                'value' => 3,
                'label' => ('HTTP_FORWARDED_FOR')
            ],
            [
                'value' => 4,
                'label' => ('HTTP_X_FORWARDED')
            ],
            [
                'value' => 5,
                'label' => ('HTTP_FORWARDED')
            ],
            [
                'value' => 6,
                'label' => ('HTTP_CLIENT_IP')
            ],
            [
                'value' => 7,
                'label' => ('HTTP_FORWARDED_FOR_IP')
            ],
            [
                'value' => 8,
                'label' => ('HTTP_PROXY_CONNECTION')
            ],
            [
                'value' => 9,
                'label' => ('VIA')
            ],
            [
                'value' => 10,
                'label' => ('X_FORWARDED_FOR')
            ],
            [
                'value' => 11,
                'label' => ('FORWARDED_FOR')
            ],
            [
                'value' => 12,
                'label' => ('X_FORWARDED')
            ],
            [
                'value' => 13,
                'label' => ('FORWARDED')
            ],
            [
                'value' => 14,
                'label' => ('CLIENT_IP')
            ],
            [
                'value' => 15,
                'label' => ('FORWARDED_FOR_IP')
            ],
        ];
    }

    /**
     * @return array
     */
    public function getOptionList() {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}