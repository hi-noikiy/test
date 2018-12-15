<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */

class Amasty_SecurityAuth_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isActive()
    {

        $ipWhiteList = explode(
            ',', Mage::getStoreConfig('amsecurityauth/general/ip_white_list')
        );
        foreach ($ipWhiteList as $k =>&$v) {
            $v = trim($v);
        }
        $isWhiteIp = in_array(
            Mage::helper('core/http')->getRemoteAddr(), $ipWhiteList);
        return (Mage::getStoreConfigFlag('amsecurityauth/general/active')
            && !$isWhiteIp);
    }

    /**
     * @param Amasty_SecurityAuth_Model_Auth $userAuth
     *
     * @return bool
     */
    public function isActiveForUser(Amasty_SecurityAuth_Model_Auth $userAuth)
    {
        return $userAuth->getEnable();
    }

}