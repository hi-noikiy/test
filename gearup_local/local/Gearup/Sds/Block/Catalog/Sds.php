<?php
class Gearup_Sds_Block_Catalog_Sds extends Mage_Rss_Block_Abstract {

    const CACHE_TAG = 'block_html_rss_catalog_sds';

    protected function _construct() {
        $this->setCacheTags(array(self::CACHE_TAG));
        $this->setCacheKey('rss_catalog_sds');
        $this->setCacheLifetime(100);
    }

    protected function _toHtml() {
        $newurl = Mage::getUrl('rss/catalog/sds');
        $title = Mage::helper('rss')->__('SDS Low Stock Products');
        $rssObj = Mage::getModel('rss/rss');
        $data = array('title' => $title,
                      'description' => $title,
                      'link' => $newurl,
                      'charset' => 'UTF-8');
        $rssObj->_addHeader($data);
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $_productCollection->addAttributeToSelect('*');
        $_productCollection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left' );
        $_productCollection->addAttributeToFilter('same_day_shipping', array('eq' => 1));
        //$_productCollection->addAttributeToFilter('qty', array('eq' => 0));
        $_productCollection->setOrder('entity_id', 'desc');
        //var_dump($_productCollection->printLogQuery(true,true));
        if ($_productCollection) {
            $args = array('rssObj' => $rssObj);
            foreach($_productCollection as $_product) {
                if (round($_product->getQty()) < $_product->getData('low_stock') || round($_product->getQty()) == 0) {
                    $args['product'] = $_product;
                    $this->addRandomXmlCallback($args);
                }
            }
        }
        return $rssObj->createRssXml();
    }

    public function addRandomXmlCallback($args) {
        $product = $args['product'];
        //$product->setData($args['row']);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit/',
            array('id' => $product->getId(), '_secure' => true, '_nosecret' => true));
        $qty = 1 * $product->getQty();
        if ($qty < $product->getData('low_stock') &&  $qty != 0) {
            $description = Mage::helper('rss')->__('Waring: %s + %s is Low on Stock reoder', $product->getName(), $product->getSku());          
        } elseif ( $qty == 0) {
            $description = Mage::helper('rss')->__('%s + %s is Out of Stock reorder immediately', $product->getName(), $product->getSku());
        }
        $rssObj = $args['rssObj'];
        $data = array(
            'title'         => $description,
            'link'          => $url,
            'description'   => $description,
        );
        $rssObj->_addEntry($data);
    }
}