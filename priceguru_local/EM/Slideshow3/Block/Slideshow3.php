<?php
class EM_Slideshow3_Block_Slideshow3 extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_slideshow3/slideshow3.phtml');
		return parent::_toHtml();
	}

	public function getSlider()
    {
		$id	=	$this->getData('slideshow');
		$slider  = Mage::getModel('slideshow3/slider')->load($id)->getData();

		return $slider;
    }

	public function getImages($images)
    {
		$images	=	unserialize(base64_decode($images));
		
		return $images;
	}
	
	
	public function getResizeImage($name,$width = 255, $height = 255){
		if(!$name) return;

		$imagePathFull = Mage::getBaseDir('media').DS.'em_slideshow'.DS.$name;
		$resizePath = $width . 'x' . $height;
		$resizePathFull = Mage::getBaseDir('media'). DS .'em_slideshow' . DS . 'resize' . DS . $resizePath . DS . $name;

		if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
			$imageObj = new Varien_Image($imagePathFull);
			$imageObj->constrainOnly(TRUE);
			$imageObj->resize($width,$height);
			$imageObj->save($resizePathFull);
		}

		return Mage::getBaseUrl('media'). 'em_slideshow/resize/' . $resizePath . "/"  . $name;	
	}

}