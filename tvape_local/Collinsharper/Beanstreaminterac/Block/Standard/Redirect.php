<?php

class Collinsharper_Beanstreaminterac_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $standard = Mage::getModel('beanstreaminterac/standard');
        
		$dataToPost = $standard->getBsRedirectCode() ;
        return $dataToPost;
    }
}