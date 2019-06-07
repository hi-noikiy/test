<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Block_Wishlist_Sidebar extends Mage_Wishlist_Block_Customer_Sidebar
{

    public function _toHtml() {
        $replace = array();
        $count = 0;
        foreach ($this->getWishlistItems() as $_item) {
            if ($_item->getProduct()) {
                $sku = $_item->getProduct()->getSku();
                $subdir = substr((string)$_item->getProduct()->getId(),0,1);
                $thumbnail = Mage::getBaseDir('media')."/configurator/cache/".$subdir."/".$_item->getProduct()->getId()."-".$sku."_115.jpg";
                $sessionid = ".".substr(Mage::getModel("core/session")->getEncryptedSessionId(),0,8);
                $thumbnail_session = Mage::getBaseDir('media')."/configurator/cache/".$subdir."/".$_item->getProduct()->getId()."-".$sku.$sessionid."_115.jpg";
                $url = "";
                if (file_exists($thumbnail_session)) {
                    $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/cache/".$subdir."/".basename($thumbnail_session);
                } else {
                    if (file_exists($thumbnail)) {
                        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/cache/".$subdir."/".basename($thumbnail);
                    }
                }
                $replace[$count] = "";
                if ($url) {
                    $replace[$count] = "g src=\"".$url."\" w";
                }
            }
            $count++;
        }

        $html = parent::_toHtml();
        if (sizeof($replace) > 0) {
            $parts = explode("<im", $html);
            $html = $parts[0]; unset($parts[0]);
            $index = 0;
            foreach ($parts as $part ) {
                if ($replace[$index]) {
                    $part = "<im" . preg_replace("/g src=\".*\" w/", $replace[$index], $part);
                } else {
                    $part = "<im".$part;
                }
                $html .= $part;
                $index++;
            }
        }

        return $html;
    }
}