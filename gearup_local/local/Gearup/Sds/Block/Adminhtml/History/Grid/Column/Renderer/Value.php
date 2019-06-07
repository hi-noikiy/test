<?php
class Gearup_Sds_Block_Adminhtml_History_Grid_Column_Renderer_Value
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
		$value = $row->getCostValue();
        $productId = $row->getProductId();
        $cost = Mage::getModel('catalog/product')->load($productId)->getCost();
		if (!$value && ($cost == '' || $cost == null)) {
            $html = '<span class="no-value" id="value'.$row->getId().'"></span>';
        }else{
            if($value)
        	$html = number_format($value, 2).' '.Mage::app()->getStore()->getBaseCurrencyCode();
        }
        
        $html .= "<script>$$('.no-value').each(function(s) {
                    var parentid = $(s).up(0);
                    parentid.addClassName('value');
                });</script>";
        return $html;
    }

}
