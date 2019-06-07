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
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Block_Cart_Item_Renderer extends Justselling_Configurator_Block_Cart_Item_Renderer_Amasty_Pure
{
    public function getProductThumbnail()
    {
        $sku = $this->getItem()->getProduct()->getSku();
        $subdir = substr((string)$this->getProduct()->getId(),0,1);

        $quoteid = NULL;
        if (Mage::registry("is_dynamic")) {
            $quoteid = ".".$this->getItem()->getQuote()->getId()."-".$this->getItem()->getId();
        }

        $selected_template_options = Mage::registry("selected_template_options");
        $template_id = Mage::registry("template_id");
        $template = Mage::getModel("configurator/template")->load($template_id);
        $js_template_id = Mage::registry("js_template_id");

        $is_configurator_product = false;
        $product_array = $template->getLinkedProducts($template_id);
        if (sizeof($product_array) > 0) {
            if (isset($product_array[$this->getItem()->getProduct()->getId()])) {
                $is_configurator_product = true;
            }
        }

        if ($is_configurator_product &&
            $template->getCombinedProductImage() &&
            $selected_template_options &&
            $template_id &&
            $js_template_id) {
            $filename = Mage::helper("configurator/combinedimage")->getCombinedProductImage($this->getItem()->getProduct(), $template, $selected_template_options, $js_template_id, Justselling_Configurator_Helper_Combinedimage::GET_PATH);
            $thumbnail = Mage::helper("configurator/combinedimage")->createThumbnail(210, 210, $filename, $subdir, $this->getItem()->getProduct(), $sku, $quoteid, "jpg");
            $url = "";
            if (file_exists($thumbnail)) {
                $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/cache/".$subdir."/".basename($thumbnail);
            }
            return $this->helper('configurator/thumbnail')->init($url);
        }

        if (!is_null($this->_productThumbnail)) {
            return $this->_productThumbnail;
        }
        return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail');
    }

    public function getOptionList(){
        if( ( $this->getItem()->getProduct()->getTypeId() == 'configurable' ) || ( $this->getItem()->getProduct()->getTypeId() == 'grouped' ) ){
            $helper = Mage::helper('catalog/product_configuration');
            $options = $helper->getConfigurableOptions($this->getItem());
            return $options;
        } else if( $this->getItem()->getProduct()->getTypeId() == 'bundle' ){
            $helper = Mage::helper('bundle/catalog_product_configuration');
            $options = $helper->getOptions($this->getItem());
            return $options;
        } else {
            return parent::getOptionList();
        }
    }
}