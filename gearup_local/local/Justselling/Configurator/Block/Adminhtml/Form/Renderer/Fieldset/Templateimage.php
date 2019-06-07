<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 28.08.13
 * Time: 09:56
 * To change this template use File | Settings | File Templates.
 */


class Justselling_Configurator_Block_Adminhtml_Form_Renderer_Fieldset_Templateimage extends Varien_Data_Form_Element_Abstract {

	/**
	 * @see Varien_Data_Form_Element_Abstract::getElementHtml()
	 */
	public function getElementHtml() {
		$_element = $this->getRenderer()->getElement();
		$templateImage = $_element->getValue();
		$imageType = $this->getData('type');

		$imageContent = '';
		if($templateImage){
			$imageContent = '<div class="uploadifyimgwrapper">
								<img id="uplodifyimg" attr-file="'.$templateImage .'" src="' .Mage::getBaseUrl('media') .$templateImage .'" />
								<a class="uploadifytag" id="uploadifytag" href="#" ></a>
							 </div>';
		}


		if($imageType == 'templateimage'){
			if($templateImage){
				$inputContent = '<div class="option-image-wrapper" style="display:none;">
									<input type="file" class="templateimage template-image" id="template-image" value="" />
								</div>';
			}else{
				$inputContent = '<div class="option-image-wrapper">
									<input type="file" class="templateimage template-image" id="template-image" value="" />
								</div>';
			}
			$html = '<div id="file-wrapper-template-image" class="file-wrapper file-wrapper-templateimage">'
						.$inputContent
						.'<div class="image-preview relative">'
							.$imageContent
						.'</div>
						<input type="hidden" id="hidden-template-image" name="template_image" value="' .$templateImage .'" class="hidden-template">
					</div>';
		}elseif($imageType == 'baseimage'){
			if($templateImage){
				$inputContent = '<div class="option-image-wrapper" style="display:none;">
									<input type="file" class="templateimage base-image" id="base-image" value="" />
								</div>';
			}else{
				$inputContent = '<div class="option-image-wrapper">
									<input type="file" class="templateimage base-image" id="base-image" value="" />
								</div>';
			}
			$html = '<div id="file-wrapper-base-image" class="file-wrapper file-wrapper-templateimage">'
				.$inputContent
						.'<div class="image-preview relative">'
							.$imageContent
						.'</div>
						<input type="hidden" id="hidden-base-image" name="base_image" value="' .$templateImage .'" class="hidden-template">
					</div>';

		}
		return $html;
	}
}