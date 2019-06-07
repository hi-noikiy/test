<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/




class Justselling_Configurator_Helper_Combinedimage extends Mage_Core_Helper_Abstract
{
	/** @var Job Status constants */
	const GET_URL     	= 0;
	const GET_PATH     	= 1;

	const TEXT_ALIGN_LEFT   			    = 1;
	const TEXT_ALIGN_CENTER   			    = 2;
	const TEXT_ALIGN_RIGHT			        = 3;

	protected function getCssTextPosition ($align, $width) {
		switch ($align) {
			case self::TEXT_ALIGN_LEFT:
				return 0;
				break;
			case self::TEXT_ALIGN_CENTER:
				return round($width/2,2);
				break;
			case self::TEXT_ALIGN_RIGHT:
				return $width;
				break;
			default:
				return false;
		}
	}

	protected function pxToIn($value_px) {
		$gdInfo = gd_info();
		if (strpos($gdInfo['GD Version'],'(2.') !== false) {
			$value_pt = $value_px;
		}else{
			$value_pt = $value_px *0.75;
		}
		$value_in = round($value_pt*1/72,2);
		return $value_in;
	}

	protected function getCssTextAlign($align) {
		$mapping = array ("1"=>"start", "2"=>"middle", "3"=>"end");
		return $mapping[$align];
	}

	protected function hexToRgb($hex) {
		$hex = preg_replace("/#/", "", $hex);
		$color = array();

		if(strlen($hex) == 3) {
			$color['r'] = hexdec(substr($hex, 0, 1));
			$color['g'] = hexdec(substr($hex, 1, 1));
			$color['b'] = hexdec(substr($hex, 2, 1));
		}
		else if(strlen($hex) == 6) {
			$color['r'] = hexdec(substr($hex, 0, 2));
			$color['g'] = hexdec(substr($hex, 2, 2));
			$color['b'] = hexdec(substr($hex, 4, 2));
		}

		return $color;
	}


	protected function _getOptionSku($templateOptions, $skuDelimiter='-') {
		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$sku = $customOptionModel->getOptionSku($templateOptions,$skuDelimiter);
		return $sku;
	}

	protected function getImageForAddOn($optionId, $jsTemplateId) {
		$mediaFolder = $mediaFolder = Mage::getBaseDir('media');
		$uploadFolder = $mediaFolder . DS . "configurator" . DS . "uploads";

		$filename = $uploadFolder . DS .  "combinedimage_".$optionId."_".$jsTemplateId.".png";

		/* Wait for the plugin image, if it is not yet there, up to 5 seconds */
		for ($i=0; $i<50;$i++) {
			if (file_exists($filename) &&  filesize($filename )>0 ) {
				return $filename;
			}
			usleep(100);
		}

		return false;
	}

	/**
	 * @param Mage_Catalog_Model_Product $_product
	 * @param Justselling_Configurator_Model_Template $template
	 * @param array $selectedTemplateOptions
	 * @param string $jsTemplateOption
	 * @param int $mode
	 * @return string
	 */
	public function getCombinedProductImage(Mage_Catalog_Model_Product $_product, Justselling_Configurator_Model_Template $template, $selectedTemplateOptions, $jsTemplateOption, $mode = self::GET_URL) {
		ini_set ( "gd.jpeg_ignore_warning", 1 );

		$sku = $_product->getSku ();
		$options_sku = $this->_getOptionSku($selectedTemplateOptions);
		if ($options_sku && strpos($_product->getSku(), $options_sku) == false) {
			$sku .= "-".$this->_getOptionSku($selectedTemplateOptions);
		}

		$base_image = $template->getBaseImage();
		$jpeg_quality = $template->getJpegQuality();

		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$customTemplateOptions = $customOptionModel->getTemplateOption(serialize(array("" => array ("template" => $selectedTemplateOptions))));

		$templateOptions = array();
        $optionIds = array();
		foreach ($customTemplateOptions as $option_arr) {
            $optionIds[] = $option_arr["option"]["id"];
			$templateOptions[] = Mage::getModel("configurator/option")->load($option_arr["option"]["id"]);
		}
        $templateOptions = Mage::getModel("configurator/option")->getCollection();
        $templateOptions->addFieldToFilter('id', array('in' => $optionIds));
        $templateOptions->setOrder('sort_order_combiimage', 'ASC');
        $templateOptions->setOrder('sort_order', 'ASC');

		$image = imagecreatefrompng ( Mage::getBaseDir ( 'media' ) . DS . $base_image );
		$imagesize = getimagesize ( Mage::getBaseDir ( 'media' ) . DS . $base_image );
		$size_x = $imagesize [0];
		$size_y = $imagesize [1];
		if (! $image)
			return (""); // No base product image is defined

		$resize_factor_x = 1.0;
		$resize_factor_y = 1.0;
		$cache = true;

		/* Adapt size of combined product image */
		if ($template->getCombinedAdaptSize()) {
			$width_option_id = $this->_getWidthOptionId($template->getId());
			$height_option_id = $this->_getHeightOptionId($template->getId());
			if ($width_option_id && $height_option_id &&
				isset($selectedTemplateOptions[$width_option_id]) &&
				isset($selectedTemplateOptions[$height_option_id])
			) {
				$factor = $template->getCombinedAdaptFactor();
				if (!$factor)
					$factor = 1.0;

				$resize_factor_x = ($selectedTemplateOptions[$width_option_id] * $factor) / $size_x ;
				$resize_factor_y = ($selectedTemplateOptions[$height_option_id] * $factor) / $size_y;

				$newimage = imagecreatetruecolor($selectedTemplateOptions[$width_option_id] * $factor,  $selectedTemplateOptions[$height_option_id] * $factor);
				imagecopyresized($newimage, $image, 0, 0, 0, 0, $selectedTemplateOptions[$width_option_id] * $factor, $selectedTemplateOptions[$height_option_id] * $factor, $size_x, $size_y);
				$image = $newimage;
				$cache = false;
			}
		}

		// check if cache directory exists
		if (! file_exists ( Mage::getBaseDir ( 'media' ) . DS . "configurator". DS ."cache". DS )) {
			$result = mkdir ( Mage::getBaseDir ( 'media' ) . DS . "configurator". DS ."cache". DS );
			Mage::Log("CONFIGURATOR: cache folder is not existing, creating...".$result);
		}
		// check if subdir for product prefix exists
		$subdir = substr ( ( string ) $_product->getId (), 0, 1 );
		if (! file_exists ( Mage::getBaseDir ( 'media' ) . DS . "configurator" . DS . "cache" . DS . $subdir )) {
			$result = mkdir ( Mage::getBaseDir ( 'media' ) . DS . "configurator" . DS . "cache" . DS . $subdir );
			Mage::Log("CONFIGURATOR: subdir-cache folder is not existing, creating ".$subdir."...".$result);
		}

		// Check if dynamic options are included

		$sessionid = "";
		$file_session = "";
		$options_count = 0;
		foreach ( $templateOptions as $option ) { // Check if dynamic elements or a plugin is included
			if ($option->getType () == "textimage") {
				$cache = false;
				break;
			}
			if ($option->getFrontendType() !== null) {
				$cache = false;
				break;
			}
			$options_count ++;
		}

		if (!$cache) {
			$sessionid = substr ( Mage::getModel ( "core/session" )->getEncryptedSessionId (), 0, 8 );
			$file_session = ".".$sessionid;
		}

		$imagetype = "jpg";
		$filename = Mage::getBaseDir('media'). DS . "configurator" . DS . "cache" . DS .$subdir. DS .$_product->getId()."-".$sku.$file_session.".".$imagetype;
		$fileurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator" . DS ."cache". DS .$subdir. DS .basename($filename);

		// Check if image already exist?
		if ($cache && file_exists ( $filename )) {
			if ($mode == self::GET_URL)
				return $fileurl;
			else
				return $filename;
		}

		foreach ( $templateOptions as $option ) {
			if ($option->getType () == "select" || $option->getType () == "selectimage" ||
				$option->getType () == "overlaymage" ||$option->getType () == "radiobuttons" ||
				$option->getType () == "selectcombi" || $option->getType () == "listimage" ||
				$option->getType () == "listimagecombi" || $option->getType () == "textimage") {

				if (isset ( $selectedTemplateOptions [$option->getId ()] )) {
					$valueId = $selectedTemplateOptions [$option->getId ()];

					$value = Mage::getModel ( 'configurator/value' )->load ( $valueId );

					if ($option->getType () == "textimage") {

						// Values from Admin
						$font_id = $option->getFont ();
						$font_size = $option->getFontSize ();
						$font_angle = $option->getFontAngle ();
						$font_base_color = $option->getFontColor ();
						$font_pos_x = $option->getFontPosX () * $resize_factor_x;
						$font_pos_y = $option->getFontPosY () * $resize_factor_y;
						$font_width_x = $option->getFontWidthX () * $resize_factor_x;
						$font_width_y = $option->getFontWidthY () * $resize_factor_y;
						$text_alignment = self::TEXT_ALIGN_LEFT;

						// Checking Session
						$dynamics = Mage::getSingleton ( 'core/session' )->getDynamics ();
						if (isset ( $dynamics [$jsTemplateOption] [$option->getId ()] )) {
							$font_conf = $dynamics [$jsTemplateOption] [$option->getId ()];
							if (isset ( $font_conf ['font'] ))
								$font_id = $font_conf ['font'];
							if (isset ( $font_conf ['font_size'] ))
								$font_size = $font_conf ['font_size'];
							if (isset ( $font_conf ['font_angle'] ))
								$font_angle = $font_conf ['font_angle'];
							if (isset ( $font_conf ['font_color'] ))
								$font_base_color = $font_conf ['font_color'];
							if (isset ( $font_conf ['text_alignment'] )){
								$text_alignment = $font_conf ['text_alignment'];
							}else{
								if($option->getTextAlignment()){
									$text_alignment = $option->getTextAlignment();
								}
							}
							if (isset ( $font_conf ['font_pos'] )) {
								$poss = explode ( "-", $font_conf ['font_pos'] );
								$font_pos_x = $poss [0] * $resize_factor_x;
								$font_pos_y = $poss [1] * $resize_factor_y;
							}
						}

						// Adapt font-size
						if ($template->getCombinedAdaptSize()) {
							if ($template->getFontAdaptFactor()) {
								$font_size = $font_size * $template->getFontAdaptFactor();
							}
						}

						$font = Mage::getModel ( "configurator/font" )->load ( $font_id );
						$font_file = Mage::getBaseDir ( 'media' ) . DS . $font->getFontFile ();
						$font_title = $font->getTitle();
						$font_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$font->getFontFile ();

						$color = $this->hexToRgb ( $font_base_color );
						$text_image = imagecreatetruecolor ( $font_width_x, $font_width_y );
						imagealphablending ( $text_image, false );
						$col = imagecolorallocatealpha ( $text_image, 255, 255, 255, 127 );
						imagefilledrectangle ( $text_image, 0, 0, $font_width_x, $font_width_y, $col );
						imagealphablending ( $text_image, true );
						$font_color = imagecolorallocate ( $text_image, $color ['r'], $color ['g'], $color ['b'] );

						$text = $valueId;

						$lines = explode ( "\n", $text );

						$current_y = 0;

						$line_height = 1.3;
						// space from origianl font size not gd calculated
						$space = $line_height * $font_size;

						$gdInfo = gd_info();
						if (strpos($gdInfo['GD Version'],'(2.') !== false) {
							$font_size = $font_size * 72 / 96;
						}

						foreach ( $lines as $line ) {
							$line = trim ( $line );
							$dimensions = imagettfbbox ( $font_size, $font_angle, $font_file, $line );
							if (! ($dimensions [0] == - 1 && $dimensions [1] == - 1 && $dimensions [2] == - 1 && $dimensions [3] == - 1)) {
								$current_x = 0;
								switch ($text_alignment) {
									case self::TEXT_ALIGN_CENTER:
										$current_x = ($font_width_x - ($dimensions [2] - $dimensions [0])) / 2;
										break;
									case self::TEXT_ALIGN_RIGHT:
										$current_x = $font_width_x - ($dimensions [2] - $dimensions [0]) - 2;
										break;
								}

								$current_y += $space;
								imagettftext ( $text_image, $font_size, 0, $current_x, $current_y, $font_color, $font_file, $line );
								$text_image = imagerotate($text_image, $font_angle, $col);
							} else {
								// Empty line
								$current_y += $space;
							}

							/* Vector Graphics Rendering */
							if ($template->getSvgExport()){
								$this->svg_export($lines, $_product, $sessionid, $template->getId(), $jsTemplateOption, $option->getId(), $template->getMassFactor(), $template->getCombinedAdaptFactor(), $selectedTemplateOptions, $font_title, $font_url, $text_alignment, $font_size, $font_base_color, $font_angle, $font_pos_x, $font_pos_y, $font_width_x, $font_width_y);
							}

						}
						imagecopy ( $image, $text_image, $font_pos_x, $font_pos_y, 0, 0, $font_width_x, $font_width_y );
					}

					$valueFilename = false;
					$plugin = false;
					if ($option->getFrontendType()) {
						$valueFilename = $this->getImageForAddOn($option->getId(), $jsTemplateOption);
						$plugin = true;
					} else {
						if ($value->getImage()) {
							$valueFilename = Mage::getBaseDir ( 'media' ) . DS . "configurator" . DS . $value->getImage();
						}
					}

					if ($valueFilename) {
						$isHexcolorImage = false;

						if(!$plugin && $this->valueIsHexColor($value->getValue())){
							$hexcode = $value->getValue();
							$rgb = $this->hex2rgb($hexcode);
							$isHexcolorImage = true;
						}else{
							$createImageFunction = $this->getImageReadFunc($this->getFileExtension(basename($valueFilename)));
							if($createImageFunction){
								$watermark =  $createImageFunction($valueFilename);
							}
						}

						if ($createImageFunction || $isHexcolorImage) {
							if($plugin || !$value->getImageSizeX() || !$value->getImageSizeY()){
								$imagesize = getimagesize($valueFilename);
								$watermarkImageSizeX = $imagesize [0];
								$watermarkImageSizeY = $imagesize [1];
							}else{
								$watermarkImageSizeX = $value->getImageSizeX();
								$watermarkImageSizeY = $value->getImageSizeY();
							}

							if($isHexcolorImage){
								$watermark = imagecreatetruecolor($watermarkImageSizeX, $watermarkImageSizeY);
								$color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
								imagefill($watermark, 0, 0, $color);
							}

							if(!$value->getImageOffsetX()){
								$watermarkImageOffsetX = 0;
							}else{
								$watermarkImageOffsetX = $value->getImageOffsetX();
							}

							if(!$value->getImageOffsetY()){
								$watermarkImageOffsetY = 0;
							}else{
								$watermarkImageOffsetY = $value->getImageOffsetY();
							}

							/* If offset if set to -1, we have to center the image */
							if ($watermarkImageOffsetX == -1) {
								$watermarkImageOffsetX = ($size_x - $watermarkImageSizeX) / 2;
							}
							if ($watermarkImageOffsetY == -1) {
								$watermarkImageOffsetY = ($size_y - $watermarkImageSizeY) / 2;
							}

							imagecopy ( $image, $watermark, $watermarkImageOffsetX, $watermarkImageOffsetY, 0, 0, $watermarkImageSizeX, $watermarkImageSizeY );
						}
					}
				}
			}
		}

		if ($options_count >= 0) {
			$result = imagejpeg ( $image, $filename, $jpeg_quality );
			Mage::Log("CONFIGURATOR: writing combined product image to cache dir...".$filename."...".$result);
			touch ( $filename );
		}

		if ($mode == self::GET_URL)
			return $fileurl;
		else
			return $filename;
	}

	protected function _getWidthOptionId($template_id) {
		$width_option = Mage::getModel("configurator/option")->getCollection()
			->addFieldToFilter("template_id",$template_id)
			->addFieldToFilter("alt_title","width");
		if ($width_option->getFirstItem())
			return $width_option->getFirstItem()->getId();
		return NULL;
	}

	protected function _getHeightOptionId($template_id) {
		$height_option = Mage::getModel("configurator/option")->getCollection()
			->addFieldToFilter("template_id",$template_id)
			->addFieldToFilter("alt_title","height");
		if ($height_option->getFirstItem())
			return $height_option->getFirstItem()->getId();
		return NULL;
	}

	public function createThumbnail($width, $height, $filename, $subdir, $_product, $sku, $file_session, $imagetype, $background_color = array(255,255,255), $overwrite = false) {
		if ($filename && file_exists($filename)) {
			$thumbnail = Mage::getBaseDir ( 'media' ) . DS . "configurator" . DS . "cache" . DS . $subdir . DS;

			if (!file_exists($thumbnail)) {
				mkdir($thumbnail);
			}

			if (is_object($_product)) {
				$thumbnail .= $_product->getId();
			} else {
				$thumbnail .= $_product;
			}
			if ($sku) { $thumbnail .=  "-" . $sku; }
			if ($file_session) { $thumbnail.=  $file_session; }
			$thumbnail .= "_".$width."." . $imagetype;
			if (in_array($imagetype, array('postscript', 'tif','tiff','bmp','eps'))) {
				$thumbnail .= ".png";
			}

			if (!file_exists($thumbnail) || $overwrite == true) {
				if (in_array($imagetype, array('png','jpg','gif','jpeg'))) {
					$thumb = new Varien_Image ( $filename );
					$thumb->backgroundColor ($background_color);
					$thumb->constrainOnly ( true );
					$thumb->keepAspectRatio ( true );
					$thumb->keepFrame ( true );
					$thumb->resize ( $width, $height );
					$thumb->save ( $thumbnail );
					touch ( $thumbnail );
				}
				if (extension_loaded('imagick') && in_array($imagetype, array('postscript', 'tif','tiff','bmp','eps'))) {
					$thumb = new imagick($filename);
					$thumb->scaleImage($width, $height, true);
					$thumb->setImageFormat('png');
					$thumb->writeImages($thumbnail, false);
				}
			}

			return $thumbnail;
		}
		return NULL;
	}

	/**
	 * @param $lines
	 * @param $product
	 * @param $session_id
	 * @param $template_id
	 * @param $js_template_id
	 * @param $option_id
	 * @param $mass_factor
	 * @param $combined_adapt_factor
	 * @param $selectedTemplateOptions
	 * @param $font_title
	 * @param $font_url
	 * @param $text_alignment
	 * @param $font_size_px
	 * @param $font_color
	 * @param $font_angle
	 * @param $font_pos_x_px
	 * @param $font_pos_y_px
	 * @param $font_width_x
	 * @param $font_width_y
	 * @return bool
	 */
	public function svg_export($lines, $product, $session_id, $template_id, $js_template_id, $option_id, $mass_factor, $combined_adapt_factor, $selectedTemplateOptions, $font_title, $font_url, $text_alignment, $font_size_px, $font_color, $font_angle, $font_pos_x_px, $font_pos_y_px, $font_width_x, $font_width_y) {
		// First read width and height options from template
		$width_option_id = $this->_getWidthOptionId($template_id);
		$height_option_id = $this->_getHeightOptionId($template_id);
		if (!$mass_factor)
			$mass_factor = 1;

		if ($width_option_id && $height_option_id) {
			$height = $selectedTemplateOptions[$height_option_id] * $mass_factor;
			$width  = $selectedTemplateOptions[$width_option_id] * $mass_factor;

			// Calculate font_pos and font_width in inch
			$font_pos_x_in = $font_pos_x_px / $combined_adapt_factor * $mass_factor;
			$font_pos_y_in = $font_pos_y_px / $combined_adapt_factor * $mass_factor;
			$font_width_x = $font_width_x / $combined_adapt_factor * $mass_factor;
			$font_width_y = $font_width_y / $combined_adapt_factor * $mass_factor;

			// Calculate font size in inch from given px
			$font_size_in = $this->pxToIn($font_size_px);

			$viewport_height = $font_width_y;
			$viewport_width = $font_width_x;

			// Calculate y position
			$y = $font_pos_y_in + $font_size_in;

			// Calculate font angle
			$transform = "";
			$font_angle =360-$font_angle;
			if ($font_angle == 360) $font_angle = 0;
			if ($font_angle != 0)
				$transform = 'transform="rotate('.$font_angle.' '.$this->getCssTextPosition($text_alignment, $width).','.$y.')';

			$svg = array();
			$svg[] = '<?xml version="1.0" standalone="no"?>';
			$svg[] = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
			$svg[] = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$width.'in" height="'.$height.'in" viewBox="0 0 '.$width.' '.$height.'">';
			$svg[] = '<defs>';
			$svg[] = '<style type="text/css"><![CDATA[';
			$svg[] = '@font-face { font-family: '.$font_title.'; src: url("'.$font_url.'"); }';
			$svg[] = ']]></style>';
			$svg[] = '</defs>';

			foreach ($lines as $line) {
				$svg[] = '<text '.$transform.' x="'.($font_pos_x_in + $this->getCssTextPosition($text_alignment, $viewport_width)).'" y="'.$y.'" style="text-anchor: '.$this->getCssTextAlign($text_alignment).'; font-family: '.$font_title.'; font-size: '.$font_size_in.'; fill: '.$font_color.';">';
				$svg[] = trim($line);
				$svg[] = '</text>';
				$y += $font_size_in;
			}

			$svg[] = '</svg>';

			/**
			$svgString = '';
			foreach($svg as $svgText){
			$svgString .= $svgText;
			}
			 */

			$file = Mage::getModel("configurator/vectorgraphics_file");
			$files = Mage::getModel("configurator/vectorgraphics_file")->getCollection()
				->addFieldToFilter("session_id", $session_id)
				->addFieldToFilter("js_template_id", $js_template_id)
				->addFieldToFilter("option_id", $option_id)
				->addFieldToFilter("status", Justselling_Configurator_Model_Vectorgraphics_File::STATUS_CREATED)
				->addFieldToFilter("quote_id", array('null' => true))
				->addFieldToFilter("quote_item_id", array('null' => true));
			if ($files->getFirstItem())
				$file->load($files->getFirstItem()->getId());

			$file->setSessionId($session_id);
			$file->setProductId($product->getId());
			$file->setTemplateId($template_id);
			$file->setJsTemplateId($js_template_id);
			$file->setOptionId($option_id);
			$file->setWidth($selectedTemplateOptions[$width_option_id]);
			$file->setHeight($selectedTemplateOptions[$height_option_id]);
			$file->setContent(serialize($lines));
			$file->setBody(serialize($svg));
			$file->setStatus(Justselling_Configurator_Model_Vectorgraphics_File::STATUS_CREATED);

			try {
				$file->save();
			} catch (Exception $e) {
				return false;
			}

			return true;
		}

		return false;
	}


	public function getImageReadFunc($filetype)
	{
		switch ($filetype) {
			case "jpg":
			case "jpeg":
				return "imagecreatefromjpeg";
				break;
			case "png":
				return "imagecreatefrompng";
				break;
			case "gif":
				return "imagecreatefromgif";
				break;
		}

		return false;
	}

    public function readImage($filename) {
        $createImageFunction = $this->getImageReadFunc($this->getFileExtension(basename($filename)));

        if($createImageFunction){
            $image =  $createImageFunction($filename);
            return $image;
        }
        return false;
    }

	public function getFileExtension($file_name)
	{
		return substr(strrchr($file_name, '.'), 1);
	}

	function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return $rgb;
	}

	public function valueIsHexColor($value){
		if(preg_match("/^#(?:[0-9a-f]{3}){1,2}$/i", $value)){
			return true;
		}else{
			return false;
		}
	}

}

