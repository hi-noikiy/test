<?php
class EM_Sliderwidget_Model_Staticblock extends Mage_Core_Model_Abstract
{
	/*
		Get List of Static Block
		return : array(
			array(
				'value'	=>	'',
				'label'	=>	''
			)
		)
	*/
	public function toOptionArray()
    {
        $options = Mage::getResourceModel('cms/block_collection')
                ->load()
                ->toOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('sliderwidget')->__('Select Block')));
        return $options;
    }
}