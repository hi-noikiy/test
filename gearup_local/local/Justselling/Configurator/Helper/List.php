<?php 
class Justselling_Configurator_Helper_List extends Mage_Core_Helper_Abstract
{
    public function getAddUrl($product)
    {
        $url = '';
        if (Mage::getStoreConfig('productconfigurator/list/active'))
            $url =  $this->_getUrl('configurator/list/addItem', array('product'=>$product->getId()));
             
        return $url;
    }
    
}