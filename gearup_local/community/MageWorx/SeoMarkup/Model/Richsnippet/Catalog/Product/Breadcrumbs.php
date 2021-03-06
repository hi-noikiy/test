<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


/**
 * At first attributes to the last element (without the link and containing a product name) are added,
 * then elements with links are consistently modified (_afterRender function)
 */
class MageWorx_SeoMarkup_Model_Richsnippet_Catalog_Product_Breadcrumbs extends MageWorx_SeoMarkup_Model_Richsnippet_Catalog_Product_Abstract
{
    protected $_crumbUri = 'seomarkup/richsnippet_catalog_product_crumb';

    protected function _addAttributeForNodes(simple_html_dom_node $node)
    {
        $parentNode = $this->_findParentContainer($node);
        if ($parentNode) {
            //$parentNode->itemscope = "";
            //$parentNode->itemtype  = "http://data-vocabulary.org/Breadcrumb";
            return true;
        }
        return false;
    }

    protected function _afterRender()
    {
        $crumb  = Mage::getModel($this->_crumbUri);
        $answer = true;
        while ($answer) {
            $answer = $crumb->render($this->_html, $this->_block);
        }
        return true;
    }

    protected function _isValidNode(simple_html_dom_node $node)
    {
        if (!$this->_findParentContainer($node)) {
            return false;
        }
        return $node;
    }

    protected function _checkBlockType()
    {
        return true;
    }

    protected function _getItemValues()
    {
        return array($this->_product->getName());
    }

}