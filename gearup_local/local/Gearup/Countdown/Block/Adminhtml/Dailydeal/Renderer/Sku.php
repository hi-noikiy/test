<?php
class Gearup_Countdown_Block_Adminhtml_Dailydeal_Renderer_Sku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $productsku =  $row->getData($this->getColumn()->getIndex());
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productsku);
        $url = '<a target="_blank" href="'.Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $product->getId())).'">'.$productsku.'</a>';
        return $url;
    }

}
