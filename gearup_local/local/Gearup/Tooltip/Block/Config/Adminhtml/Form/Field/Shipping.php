<?php

class Gearup_Tooltip_Block_Config_Adminhtml_Form_Field_Shipping extends Mage_Core_Block_Html_Select
{
    public function _toHtml()
    {
        $shippings = Mage::getSingleton('shipping/config')->getActiveCarriers();

        foreach ($shippings as $shippingCode=>$shippingModel) {
            $shippingTitle = Mage::getStoreConfig('carriers/'.$shippingCode.'/title');
            $this->addOption($shippingCode, $shippingTitle);
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}