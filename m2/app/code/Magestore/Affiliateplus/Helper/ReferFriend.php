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
namespace Magestore\Affiliateplus\Helper;

/**
 * Helper Refer Friend
 */
class ReferFriend extends HelperAbstract
{
    /**
     * @return bool
     */
    public function disableReferFriend() {
        if (!$this->getConfig('affiliateplus/refer/enable')) {
            return true;
        }
        if ($this->_objectManager->create('Magestore\Affiliateplus\Helper\Account')->isNotAvailableAccount()) {
            return true;
        }
        return false;
    }
}
