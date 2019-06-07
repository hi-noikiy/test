<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */ 
class Amasty_Scroll_Model_Observer
{
    public function handleLayoutRender()
    {
        if (!Mage::app()->getRequest()->getParam('is_scroll')) {
            return;
        }
        $layout = Mage::getSingleton('core/layout');
        if (!$layout) {
            return;
        }
            
        $isAJAX = Mage::app()->getRequest()->getParam('is_ajax', false);
        $isAJAX = $isAJAX && Mage::app()->getRequest()->isXmlHttpRequest();
        if (!$isAJAX) {
            return;
        }
            
        $layout->removeOutputBlock('root');    
        Mage::app()->getFrontController()->getResponse()->setHeader('content-type', 'application/json');

        $html = "";
        /*compatibility with Amasty Finder*/
        $finder = $layout->getBlock('amfinder89');
        if ($finder) {
            $html = $finder->toHtml();
        }

        $page = Mage::helper('amscroll')->findProductList($layout);
        if (!$page) {
            return;
        }

        $swatchesBlock = $layout->getBlock('configurableswatches.media.js.list');
        if ($swatchesBlock) {
            $swatchesBlock->setCurrentPage((int)Mage::app()->getRequest()->getParam('p'));
        }

        $html .= $page->toHtml();
        //$swatchesBlock = Mage::app()->getLayout()->createBlock("configurableswatches/catalog_media_js_list", "configurableswatches.media.js.list");
        //$html .= $swatchesBlock->toHtml();

        $response =  array(
            'page' => $this->_removeAjaxParam($html)
        );

        /* compatibility with amasty labels */
        if (Mage::helper('core')->isModuleEnabled('Amasty_Label')) {
            $label = $layout->getBlock('amlabel_script');
            if ($label) {
                $response['amlabel_script'] = $label->toHtml();
            }
        }

        /* compatibility with amasty zoom pro */
        if (Mage::helper('core')->isModuleEnabled('Amasty_Zoom')) {
            $amZoomerBlock = $layout->createBlock('amzoom/grid');
            if ($amZoomerBlock) {
                $response['zoom_information'] = $amZoomerBlock->getProductInformation();
            }
        }

        $response = Mage::helper('core')->jsonEncode($response);

        Mage::app()->getResponse()->setBody($response)->sendResponse();
        if (class_exists('Amasty_Base_Helper_Utils')) {
            Mage::helper('ambase/utils')->_exit();
        }
    }
    
    protected function _removeAjaxParam($html)
    {
        $html = str_replace('is_ajax=1&amp;', '', $html);
        $html = str_replace('is_ajax=1&',     '', $html);
        $html = str_replace('?is_ajax=1',     '', $html);
        $html = str_replace('is_ajax=1',      '', $html);

        $html = str_replace('?___SID=U', '', $html);
        $html = str_replace('___SID=U',  '', $html);
        
        return mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html));
    }
}
