<?php
class Gearup_Sds_Block_Adminhtml_History_Grid_Column_Renderer_Cost
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
		$value = $row->getCost();
		$productId = $row->getProductId();
		$cost = Mage::getModel('catalog/product')->load($productId)->getCost();
		if (!$value && ($cost == '' || $cost == null)) {
            $html = '<span class="no-cost" id="cost'.$row->getId().'"></span>';
        }else{
        	if($value)
        	$html = number_format($value, 2).' '.Mage::app()->getStore()->getBaseCurrencyCode();
        }
        
        $html .= "<script>$$('.no-cost').each(function(s) {
                    var parentid = $(s).up(0);
                    parentid.addClassName('cost');
                });</script>";
        return $html;
    }

}
