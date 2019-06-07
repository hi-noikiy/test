<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Block_Page_Html extends Mage_Page_Block_Html
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if(Mage::registry('current_product') && Mage::getStoreConfig('mageworx_seo/seosuite/product_og_enabled')) {
            $headAttributesAsString = 'prefix = "og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# product: http://ogp.me/ns/product#"';
            $pos = strpos($html, "<head");
            if($pos !== false){
            	$html = substr_replace($html, "<head " . $headAttributesAsString, $pos, 5);
            }
        }
        return $html;
    }
}
