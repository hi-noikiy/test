<?php


class MindMagnet_Conf_Helper_Data extends Mage_Core_Helper_Data
{
    public function setConfigurableId($id)
    {
        if ($id) {
            Mage::getSingleton('core/session')->setConfigurableId($id);   
        }
    }
    
    
    public function getConfigurableId()
    {
        return Mage::getSingleton('core/session')->getConfigurableId();
    }

}