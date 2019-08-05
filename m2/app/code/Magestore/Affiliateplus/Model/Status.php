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
namespace Magestore\Affiliateplus\Model;

/**
 * Model Status
 */
class Status
{
    const STATUS_ENABLED = '1';

    const STATUS_DISABLED = '2';

    const STATUS_YES = '1';

    const STATUS_NO = '0';



    /**
     * Get available statuses.
     *
     * @return void
     */
    public static function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public static function getYesNoOption(){
        return [
            self::STATUS_YES => __('Yes'),
            self::STATUS_NO => __('No')

        ];
    }
}
