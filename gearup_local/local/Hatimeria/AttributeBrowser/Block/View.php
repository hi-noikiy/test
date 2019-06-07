<?php

/**
 * Attribute View
 */
class Hatimeria_AttributeBrowser_Block_View extends Mage_Core_Block_Template
{
    /**
     * @see Mage_Core_Block_Template
     * @return \Hatimeria_AttributeBrowser_Block_View 
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $layout = $this->getLayout();
        $attribute = $this->getCurrentAttribute();

        $breadcrumbs = $layout->createBlock('page/html_breadcrumbs');
        $breadcrumbs->addCrumb('home', array(
            'label' => Mage::helper('catalog')->__('Home'),
            'title' => Mage::helper('catalog')->__('Go to Home Page'),
            'link' => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb('attribute_code', array(
            'label' => Mage::helper('catalog')->__($attribute['title']),
            'title' => Mage::helper('catalog')->__($attribute['title']),
            'readonly' => true
        ));
        
        $layout->getBlock('root')->setChild('breadcrumbs', $breadcrumbs);
        
        $head = $layout->getBlock('head');
        $head->setTitle($attribute['title']);
        $head->setMetaDescription($attribute['description']);
        $head->setMetaKeywords($attribute['keywords']);

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
     * Attribute Title 
     */
    public function getTitle()
    {
        $attr = $this->getCurrentAttribute();
        
        return $attr['title'];
    }
}
