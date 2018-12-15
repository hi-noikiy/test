<?php
class EM_Apiios_Model_Api2_Slideshow_Rest_Guest_V1 extends Mage_Api2_Model_Resource
{
    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $result = array(
            'list_image'=>array(
            array('img_src' =>  Mage::getBaseUrl('media').'slideshow/1.jpg'),
            array('img_src' =>  Mage::getBaseUrl('media').'slideshow/2.jpg'),
            array('img_src' =>  Mage::getBaseUrl('media').'slideshow/3.jpg'),
            array('img_src' =>  Mage::getBaseUrl('media').'slideshow/4.jpg'),
            array('img_src' =>  Mage::getBaseUrl('media').'slideshow/5.jpg')
                )
        );
    	return $result;
    }
}
?>