<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tab_Slideshow2 extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_mobapp/manage_slide2.phtml');

		$data	=	Mage::registry('mobapp_data');
		$slide	=	Mage::helper("mobapp")->jsonDecode($data->getData('slideshow3'));
		$slide2	=	Mage::helper("mobapp")->jsonDecode($data->getData('slideshow4'));

		if($slide)	$count	=	count($slide);
		else	$count	=	0;

		if($slide2)	$count2	=	count($slide2);
		else	$count2	=	0;

		$this->assign('count', $count);
		$this->assign('slide', $slide);

		$this->assign('count2', $count2);
		$this->assign('slide2', $slide2);
		return parent::_toHtml();
	}

	public function getResizeImage($name,$level="",$width = 255, $height = 255){
		if(!$name) return;

		$imagePathFull = Mage::getBaseDir('media') . DS . 'em_mobapp' . DS . 'slideshow'.$level . DS . $name;
		$resizePath = $width . 'x' . $height;
		$resizePathFull = Mage::getBaseDir('media'). DS .'em_mobapp' . DS .'slideshow'.$level . DS . 'resize' . DS . $resizePath . DS . $name;

		if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
			$imageObj = new Varien_Image($imagePathFull);
			$imageObj->constrainOnly(TRUE);
			$imageObj->resize($width,$height);
			$imageObj->save($resizePathFull);
		}

		return Mage::getBaseUrl('media'). 'em_mobapp/slideshow'.$level.'/resize/' . $resizePath . "/"  . $name;	
	}
}