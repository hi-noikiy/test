<?php
class Gearup_Sds_Block_Adminhtml_Sds_Grid_Column_Renderer_Manufacturer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if (!$value) {
            return '';
        }
        $product = Mage::getModel('catalog/product')->setStoreId(1)->setData('manufacturer',$value);
        $option_label = $product->getAttributeText('manufacturer');
        return $option_label;
    }

}
