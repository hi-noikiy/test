<?php
class Gearup_Sds_Block_List extends Mage_Rss_Block_List {
    public function getRssMiscFeeds() {
        $this->resetRssFeed();
        $this->NewProductRssFeed();
        $this->SpecialProductRssFeed();
        $this->SalesRuleProductRssFeed();
        $this->SdsRssFeed();
        return $this->getRssFeeds();
    }
    public function SdsRssFeed() {
        $path = self::XML_PATH_RSS_METHODS.'/catalog/sds';
        if ((bool) Mage::getStoreConfig($path)) {
            $this->addRssFeed($path, $this->__('SDS Low Stock Products'));
        }
    }
}