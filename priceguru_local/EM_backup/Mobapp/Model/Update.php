<?php
class EM_Mobapp_Model_Update extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mobapp/update');
    }
	
	public function version($ver="")
    {
		if($ver == "1.0.1")	$this->version_101();

        return true;
    }

	protected function version_101()
    {
		$helper = Mage::helper("mobapp");
		$collection =  Mage::getModel('mobapp/store')->getCollection();

		if($collection->getSize() > 0){
			foreach($collection as $value){
				$slideshow	=	unserialize($value->getSlideshow());
				$slideshow2	=	unserialize($value->getSlideshow2());
				$slideshow3	=	unserialize($value->getSlideshow3());
				$slideshow4	=	unserialize($value->getSlideshow4());
				$theme		=	unserialize($value->getTheme());
				$paygate	=	unserialize($value->getPaygate());

				if($slideshow != "")	$value->setSlideshow(json_encode($slideshow,JSON_HEX_TAG));
				if($slideshow2 != "")	$value->setSlideshow2(json_encode($slideshow2,JSON_HEX_TAG));
				if($slideshow3 != "")	$value->setSlideshow3(json_encode($slideshow3,JSON_HEX_TAG));
				if($slideshow4 != "")	$value->setSlideshow4(json_encode($slideshow4,JSON_HEX_TAG));
				if($theme != "")		$value->setTheme(json_encode($theme,JSON_HEX_TAG));
				if($paygate != "")		$value->setPaygate(json_encode($paygate,JSON_HEX_TAG));

				$value->save();
			}
		}

		return true;
    }

}