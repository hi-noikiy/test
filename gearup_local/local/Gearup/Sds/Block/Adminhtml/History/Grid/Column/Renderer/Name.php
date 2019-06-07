<?php
class Gearup_Sds_Block_Adminhtml_History_Grid_Column_Renderer_Name
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
		/*$sku = $row->getSku();
		if(empty($sku)){
			$product = Mage::getModel('catalog/product')->load($row->getProductId());
		}else{
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		}
		return $product->getName();*/

		$productId = $row->getProductId();
		$product = Mage::getModel('catalog/product')->load($productId);
		return $product->getName();
    }

}
