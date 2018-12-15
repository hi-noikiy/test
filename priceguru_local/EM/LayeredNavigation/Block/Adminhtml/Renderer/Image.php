<?php
class EM_LayeredNavigation_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
		$image = $row->getData('image');
		if (empty($image)) return '';
		$path = Mage::helper('layerednavigation')->getImageUrl($image);
    	return '<img src="'.$path.'" alt="" class="sample" />';
    }

}