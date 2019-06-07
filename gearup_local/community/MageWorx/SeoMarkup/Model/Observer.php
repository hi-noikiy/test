<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoMarkup_Model_Observer
{
    public function createOpenGraphMarkup($observer)
    {
        if(!Mage::helper('seomarkup/config')->isOpenGraphProtocolEnabled()){
            return;
        }

        if(Mage::helper('seomarkup')->getCurrentFullActionName() != 'catalog_product_view'){
            return;
        }

        $block     = $observer->getBlock();
        $transport = $observer->getTransport();

        if($block->getNameInLayout() == 'head'){
            $product = Mage::registry('current_product');
            if($product && $product->getId()){

                $url              = Mage::helper('seomarkup')->getProductCanonicalUrl($product);
                $doubleQuoteTitle = '"';
                $doubleQuoteDescr = '"';
                $descr            = strip_tags($product->getShortDescription() ? $product->getShortDescription() : $product->getDescription());
                $title            = strip_tags($product->getName());

                if (strpos($title, $doubleQuoteTitle) !== false) {
                    $doubleQuoteTitle = "'";
                }
                elseif (strpos($descr, $doubleQuoteDescr) !== false) {
                    $doubleQuoteDescr = "'";
                }

                $productTypeId = $product->getTypeId();

                if ($productTypeId == 'bundle') {
                    $prices = Mage::helper('seomarkup')->getBundlePrices();
                }
                elseif ($productTypeId == 'grouped') {
                    $prices = Mage::helper('seomarkup')->getGroupedPrices();
                }
                elseif($productTypeId == 'giftcard'){
                	$prices = Mage::helper('seomarkup')->getGiftcardPrices();
                }
                else {
                    $prices = Mage::helper('seomarkup')->getDefaultPrices();
                }

                if (!empty($prices) && is_array($prices)) {
                    $price = $prices[0];
                }

                $currency = strtoupper(Mage::app()->getStore()->getCurrentCurrencyCode());
                $ogs  ="\n";
                $ogs .= "<meta property=\"og:type\" content=\"product\"/>\n";
                $ogs .= "<meta property=\"og:title\" content=$doubleQuoteTitle" . $title . "$doubleQuoteTitle/>\n";
                $ogs .= "<meta property=\"og:description\" content=$doubleQuoteDescr" . $descr . "$doubleQuoteDescr/>\n";
                $ogs .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";

                if ($price) {
                    $ogs .= "<meta property=\"product:price:amount\" content=\"" . $price . "\"/>\n";
                }
                if ($currency) {
                    $ogs .= "<meta property=\"product:price:currency\" content=\"" . $currency . "\"/>\n";
                }

                $ogs .= "<meta property=\"og:image\" content=\"" . Mage::helper('catalog/image')->init($product, 'image') . "\"/>\n";

                $transport->setHtml($transport->getHtml() . $ogs);
           }
       }
    }

    public function createRichsnippetProductMarkup($observer)
    {
        if(Mage::helper('seomarkup')->getCurrentFullActionName() == 'catalog_product_view'){
            if (!Mage::helper('seomarkup/config')->isRichsnippetDisabled()) {
                $block = $observer->getBlock();

                if((Mage::helper('seomarkup/config')->isRichsnippetEnabled() && $this->_getHandlerForBlock($block->getNameInLayout()))){
                    $injection = true;
                }elseif(Mage::helper('seomarkup/config')->isRichSnippetOnlyBreadcrumbsEnabled() && ($block->getNameInLayout() == 'breadcrumbs')){
                    $injection = true;
                }

                if (!empty($injection) && Mage::helper('seomarkup/config')->isProductPage()) {
                    $transport    = $observer->getTransport();
                    $normalOutput = $observer->getTransport()->getHtml();
                    $modelUri     = $this->_getHandlerForBlock($block->getNameInLayout());

                    $model        = Mage::getModel($modelUri);
                    //echo $modelUri; exit;

                    $modifyOutput = $model->render($normalOutput, $block, true);
                    if ($modifyOutput) {
                        $transport->setHtml($modifyOutput);
                    }
                }
            }
        }
    }

    public function createRichsnippetReviewMarkup($observer)
    {
        if(Mage::getStoreConfigFlag('advanced/modules_disable_output/Mage_Review')){
            return;
        }

        if(Mage::helper('seomarkup')->getCurrentFullActionName() !== 'catalog_product_view'){
            return;
        }

        if (!Mage::helper('seomarkup/config')->isRichsnippetEnabled()) {
            return;
        }

        $block = $observer->getBlock();

        if($block instanceof Mage_Review_Block_Helper){
            $transport    = $observer->getTransport();
            $normalOutput = $observer->getTransport()->getHtml();

            $model = Mage::getModel('seomarkup/richsnippet_catalog_product_review');

            $modifyOutput = $model->render($normalOutput, $block, true);
            if ($modifyOutput) {
                $transport->setHtml($modifyOutput);
            }
        }
    }

    /**
     * @param type $blockName
     * @return mixed
     */
    protected function _getHandlerForBlock($blockName)
    {
        $handlers = array(
            'breadcrumbs'   => 'seomarkup/richsnippet_catalog_product_breadcrumbs',
            'product.info'  => 'seomarkup/richsnippet_catalog_product_product',
        );

        if(!empty($handlers[$blockName])){
            return $handlers[$blockName];
        }
        return null;
    }
}
