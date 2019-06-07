<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoBase_Block_Page_Html_Pager extends MageWorx_SeoBase_Block_Page_Html_Pager_Abstract
{
    public function getPagerUrl($params = array())
    {
        if(!Mage::getStoreConfigFlag('mageworx_seo/seosuite/reviews_friendly_urls')){
            return parent::getPagerUrl($params);
        }

        if(strpos(Mage::app()->getRequest()->getOriginalPathInfo(), 'reviews') === false){
            return parent::getPagerUrl($params);
        }

        $urlParams                 = array();
        $urlParams['_current']     = true;
        $urlParams['_escape']      = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query']       = $params;

        $url = $this->getUrl('*/*/*', $urlParams);
        return str_replace('/reviews', Mage::app()->getRequest()->getOriginalPathInfo(), $url);
    }
}