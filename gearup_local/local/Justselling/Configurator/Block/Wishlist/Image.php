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

class Justselling_Configurator_Block_Wishlist_Image extends Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Image
{

    public function _toHtml() {
        if ($this->getItem()->getProduct()) {
            $sku = $this->getItem()->getProduct()->getSku();
            $subdir = substr((string)$this->getItem()->getProduct()->getId(),0,1);

            $wishlist_id = NULL;
            if (Mage::registry("is_dynamic")) {
                $wishlist_id = ".".$this->getItem()->getWishlistId()."-".$this->getItem()->getWishlistItemId();
            }

            $selected_template_options = Mage::registry("selected_template_options");
            $template_id = Mage::registry("template_id");
            $template = Mage::getModel("configurator/template")->load($template_id);
            $js_template_id = Mage::registry("js_template_id");

            if ($template->getCombinedProductImage() && $selected_template_options && $template_id && $js_template_id) {
                $filename = Mage::helper("configurator/combinedimage")->getCombinedProductImage($this->getItem()->getProduct(), $template, $selected_template_options, $js_template_id, Justselling_Configurator_Helper_Combinedimage::GET_PATH);
                $thumbnail = Mage::helper("configurator/combinedimage")->createThumbnail(115, 115, $filename, $subdir, $this->getItem()->getProduct(), $sku, $wishlist_id, "jpg");
                $url = "";
                if (file_exists($thumbnail)) {
                    $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/cache/".$subdir."/".basename($thumbnail);
                }
                if ($url) {
                    $html = parent::_toHtml();
                    $html = preg_replace("/img src=\".*\" w/", "img src=\"".$url."\" w", $html);
                    return $html;
                } else {
                    return parent::_toHtml();
                }
            }
        }
        return parent::_toHtml();
    }

}