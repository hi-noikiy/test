<?php

/**
 * AttributeBrowser View
 */
class Hatimeria_AttributeBrowser_Block_Item_View extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $layout = $this->getLayout();
        
        $item = $this->getCurrentItem();
        $attr = $this->getCurrentAttribute();

        //$breadcrumbs = $layout->createBlock('page/html_breadcrumbs');
        $breadcrumbs = $layout->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array(
            'label' => Mage::helper('catalog')->__('Home'),
            'title' => Mage::helper('catalog')->__('Go to Home Page'),
            'link' => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb('shopby', array(
            'label' => Mage::helper('catalog')->__($attr['title']),
            'title' => Mage::helper('catalog')->__($attr['title']),
            'link' => $this->getUrl($attr['route'])
        ));
        
        $breadcrumbs->addCrumb('brand', array(
            'label' => $item['name'],
            'title' => $item['name'],
            'readonly' => true
        ));
        
        $layout->getBlock('root')->setChild('breadcrumbs', $breadcrumbs);
        $layout->getBlock('head')->setTitle($item['name']);

        return $this;
    }
    
    /**
     * Current attribute
     * 
     * @return array
     */
    public function getCurrentAttribute()
    {
        return Mage::getSingleton('attributebrowser/list')->getCurrentAttribute();
    }
    
    /**
     * Current item
     * 
     * @return array
     */
    public function getCurrentItem()
    {
        return Mage::getSingleton('attributebrowser/list')->getCurrentItem();
    }
    
    /**
     * Brand Name
     * 
     * @return string
     */
    public function getName()
    {
        $item = $this->getCurrentItem();
        
        return $item['name'];
    }
}