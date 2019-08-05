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

class Refer
{
    const REFER_FRIEND_BY_EMAIL = 'email';
    const REFER_FRIEND_BY_FACEBOOK = 'facebook';
    const REFER_FRIEND_BY_GOOGLE = 'google';
    const REFER_FRIEND_BY_TWITTER = 'twitter';
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::REFER_FRIEND_BY_EMAIL,
                'label'=>__('Email')
            ],
            [
                'value' => self::REFER_FRIEND_BY_FACEBOOK,
                'label'=>__('Facebook')
            ],
            [
                'value' => self::REFER_FRIEND_BY_TWITTER,
                'label'=>__('Twitter')
            ],
            [
                'value' => self::REFER_FRIEND_BY_GOOGLE,
                'label'=>__('Google')
            ]
        ];
    }
}