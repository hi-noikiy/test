<?php

/**
 * Sitemap
 */
class Hatimeria_AttributeBrowser_Block_Sitemap extends Hatimeria_AttributeBrowser_Block_Item_List implements Hatimeria_FrontSitemap_Block_ISitemap
{
    protected function _construct()
    {
        parent::_construct();
        $this->unsetData('cache_lifetime');
        $this->unsetData('cache_tags');
    }
    
    public function getTitle()
    {
        return $this->__('Shop By Goal');
    }
    
    public function getSitemapUrl()
    {
        return $this->getUrl('shopbygoal');
    }
    
    public function getItems()
    {
        $this->setCode('goal');
        $items = parent::getItems();
        $links = array();
        
        foreach ($items as $item) {
            $links[] = new Varien_Object(array(
                'url' => $this->getUrl('shopbygoal/'.$item['key']),
                'title' => $item['name'],
            ));
        }
        
        return $links;
    }
}