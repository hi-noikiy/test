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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Model_Product_Option_Type_Custom extends Mage_Catalog_Model_Product_Option_Type_Default
{
	/** @var If captured, the template ID will be cached (to get all options via collection) */
	private $_cacheTemplateId;

	/** Load collection only once! */
	/** @var $_cacheOptionCollection Justselling_Configurator_Model_Mysql4_Option_Collection option collection only one! */
	private $_cacheOptionCollection;

	public function getJsTemplateId($optionValue) {
		if (is_string($optionValue)) {
			$myArray =  unserialize($optionValue);
		} else {
			$myArray = $optionValue;
		}

		if (!is_array($myArray))
			return false;

		foreach ($myArray as $key=>$optionValues) {
			return $key;
		}
	}

	public function getDynamics($jsTemplateId, $optionId, $optionValue) {
		if ($optionValue) {
			$arr =  unserialize($optionValue);
		}

		if (!is_array($arr))
			return false;

		if (isset($arr[$jsTemplateId]['dynamics']) && isset($arr[$jsTemplateId]['dynamics'][$optionId])) {
			return $arr[$jsTemplateId]['dynamics'][$optionId];
		}

		return false;
	}

	protected function setDynamics($jsTemplateId, $optionId, $value) {
		$dynamics = Mage::getSingleton('core/session')->getDynamics();

		if (isset($dynamics[$jsTemplateId]) && isset($dynamics[$jsTemplateId][$optionId])) {
			$dynamics[$jsTemplateId][$optionId] = $value;
			Mage::getSingleton('core/session')->setDynamics($dynamics);
		}
	}

	/**
	 * @param $optionId
	 * @return bool|Mage_Core_Model_Abstract|Varien_Object
	 */
	private function getOptionForId($optionId) {
		if (!empty($this->_cacheOptionCollection)) {
			return $this->_cacheOptionCollection->getItemById($optionId);
		} elseif (!empty($this->_cacheTemplateId)) {
			$optionCollection = Mage::getModel('configurator/option')->getCollection();
			$optionCollection->addFieldToFilter('template_id', $this->_cacheTemplateId);
			$optionCollection->addFieldToSelect('*');
			$this->_cacheOptionCollection = $optionCollection;
			return $this->_cacheOptionCollection->getItemById($optionId);
		} else {
			$option = Mage::getModel('configurator/option')->load($optionId);
			if ($option->getId()) {
				$this->_cacheTemplateId = $option->getTemplateId();
				return $option;
			}
			return false;
		}
	}

	public function getTemplateOption($optionValue) {
		$arr =  unserialize($optionValue);
		if (!is_array($arr))
			return false;

		$cacheKey = "PRODCONF_OPTIONS_".md5($optionValue);
		if ($cacheArray = Mage::helper("configurator")->readFromCache($cacheKey, true)) {

			$selectedTemplateOptions = $cacheArray['selected_template_options'];
			$js_template_id = $cacheArray['js_template_id'];
			$template_id = $cacheArray['template_id'];
			$isDynamic = $cacheArray['is_dynamic'];
			$options = $cacheArray['options'];

			if ($selectedTemplateOptions) {
				if(Mage::registry("selected_template_options")){
					Mage::unregister("selected_template_options");
				}
				Mage::register("selected_template_options", $selectedTemplateOptions);
			}
			if ($js_template_id) {
				if(Mage::registry("js_template_id")){
					Mage::unregister("js_template_id");
				}
				Mage::register("js_template_id", $js_template_id);
			}
			if ($template_id) {
				if(Mage::registry("template_id")){
					Mage::unregister("template_id");
				}
				Mage::register("template_id", $template_id);
			}
			if ($isDynamic) {
				if(Mage::registry("is_dynamic")){
					Mage::unregister("is_dynamic");
				}
				Mage::register("is_dynamic", $isDynamic);
			}
			return $options;
		}

		$options = array();
		$selectedTemplateOptions = array();
		$isDynamic = false;
		$allOptionIds = array();
		foreach ($arr as $js_template_id =>$optionValues) {
			foreach($optionValues as $valueKey => $optionValue) {
				if ($valueKey == "postprice") {

					$options[] = array(
						'option' => array("title" => "postprice", "is_visible" => 0),
						'value' => array(
							'title'=> "__postprice",
							'price'=> $optionValue,
							'sku' => ""
						)
					);
				}
				if( $valueKey == "template" ) {
					$allOptionIds = array_keys($optionValue);
					foreach ($optionValue as $optionId => $valueId ) {
						$optionModel = null;
						$optionModel = $this->getOptionForId($optionId);
						if (empty($optionModel)) { // can be ignored as in previous version
							continue;
						}
						$template_id = $optionModel->getTemplateId();
						$orgValueId = null;

						if ($optionModel->getFrontendType() !== null) {
							$isDynamic = true;
						}

						if($optionModel->getId() &&  !empty($valueId)) {
							if ($optionModel->getOperator() === 'expression' && ($optionModel->getType() === 'selectcombi' || $optionModel->getType() === "listimagecombi" || $optionModel->getType() === "overlayimagecombi")) {
								$orgValueId = $valueId;
								$expressionKey = "expression-".$optionId;
								$valueId = $optionValue[$expressionKey];
							} else {
								$expressionKey= null;
							}

							$price = 0;
							/* Get option price */
							switch ($optionModel->getType()) {
								case "textimage":
									$price =  $optionModel->getCalculatedPrice($valueId,$optionValue);
									$isDynamic = true;
									break;
								case "area":
								case "text":
								case "static":
								case "combi":
								case "matrixvalue":
								case "expression":
								case "http":
								case "checkbox":
								case "date":
                                case "productattribute":
									$price =  $optionModel->getCalculatedPrice($valueId,$optionValue);
									break;
								case "selectcombi":
								case "listimagecombi":
								case "overlayimagecombi":
									$price = $optionModel->getCalculatedPrice($valueId,$optionValue);
									break;
								case "listimage":
								case "select":
								case "radiobuttons":
								case "selectimage":
								case "overlayimage":
									$valueModel = Mage::getModel('configurator/value')->load($valueId);
									$price = $valueModel->getPrice();
									break;
							}
							$option = null;
							try {
								$option = $this->getOption();
							} catch (Exception $e) {
							}
							if ($option && $option instanceof Mage_Catalog_Model_Product_Option) {
								$product_id = $this->getOption()->getProductId();
								$price = Mage::helper('configurator')->getDiscountPrice($optionModel, $price, $product_id);
							} else {
								$price = Mage::helper('configurator')->getDiscountPrice($optionModel, $price);
							}

							/* build option array */
							switch ($optionModel->getType()) {
								case "file":
								case "area":
								case "text":
								case "static":
								case "combi":
								case "matrixvalue":
								case "expression":
								case "http":
								case "textimage":
								case "checkbox":
								case "date":
                                case "productattribute":
                                    $sku = $optionModel->getSku($valueId);
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=>$valueId,
											'value'=>$valueId,
											'price'=> $price,
											'sku' => $sku
										)
									);

									if($optionModel->getType() == "textimage"){
										$valueId = Mage::helper('configurator')->ieLinebreakFix($valueId);
									}
									$selectedTemplateOptions[$optionModel->getId()] = $valueId;

									break;
								case "selectcombi":
								case "listimagecombi":
								case "overlayimagecombi":
									if ($optionModel->getOperator() === 'expression' && ($optionModel->getType() === 'selectcombi'  || $optionModel->getType() === 'listimagecombi' || $optionModel->getType() === 'overlayimagecombi')) {
										$valueModel = Mage::getModel('configurator/value')->load($orgValueId);
									} else {
										$valueModel = Mage::getModel('configurator/value')->load($valueId);
									}
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=> $valueModel->getTitle(),
											'value'=> $valueModel->getValue(),
											'product_id'=> $valueModel->getProductId(), // may be empty
											'price'=> $price,
											'sku'=> $valueModel->getSku()
										)
									);
									$selectedTemplateOptions[$optionModel->getId()] = $valueModel->getId();
									break;
								case "listimage":
								case "select":
								case "selectimage":
								case "overlayimage":
								case "radiobuttons":
									$options[] = array(
										'option' => $optionModel->getData(),
										'value' => array(
											'title'=> $valueModel->getTitle(),
											'value'=> $valueModel->getValue(),
											'product_id'=> $valueModel->getProductId(), // may be empty
											'price'=> $price,
											'sku' => $valueModel->getSku()
										)

									);
									$selectedTemplateOptions[$optionModel->getId()] = $valueModel->getId();
									break;
							}
						}
					}
				}
			}
		}

		$cacheArray = array();

		/* store select template options and js-option in registry */
		if ($selectedTemplateOptions) {
			if (Mage::registry("selected_template_options") != null) {
				Mage::unregister("selected_template_options");
			}
			Mage::register("selected_template_options", $selectedTemplateOptions);
		}
		if ($js_template_id) {
			if (Mage::registry("js_template_id") != null) {
				Mage::unregister("js_template_id");
			}
			Mage::register("js_template_id", $js_template_id);
		}
		if ($template_id) {
			if (Mage::registry("template_id") != null) {
				Mage::unregister("template_id");
			}
			Mage::register("template_id", $template_id);
		}
		if ($isDynamic) {
			if (Mage::registry("is_dynamic") != null) {
				Mage::unregister("is_dynamic");
			}
			Mage::register("is_dynamic", $isDynamic);
		}

		$cacheArray['selected_template_options'] = $selectedTemplateOptions;
		$cacheArray['js_template_id'] = $js_template_id;
		$cacheArray['template_id'] = $template_id;
		$cacheArray['is_dynamic'] = $isDynamic;
		$cacheArray['options'] = $options;

		$tags = array();
		foreach ($allOptionIds as $optionId) { $tags[] = "PRODCONF_OPTION_".$optionId; }
		$tags[] = "PRODCONF";
		Mage::helper("configurator")->writeToCache($cacheArray, $cacheKey, $tags, true);
		return $options;
	}

	/**
	 * (non-PHPdoc)
	 * @see Mage_Catalog_Model_Product_Option_Type_Default::getOptionPrice()
	 */
	public function getOptionPrice($optionValue, $basePrice)
	{
		$templateOptions = NULL;
		if( is_array($optionValue) ) {
			$optionValue = serialize(array($this->getJsTemplateOption($optionValue) => array('template' => $optionValue)));
			$templateOptions = $this->getTemplateOption($optionValue);
		} else {
			$templateOptions = $this->getTemplateOption($optionValue);
		}

		$optionPrice = 0;
		foreach ($templateOptions as $templateOption) {
			$optionPrice += (float) $templateOption['value']['price'];
		}
		return $optionPrice;
	}

	public function setUserValue($value)
	{
		if (Mage::getSingleton('core/session')->getDynamics()) {
			foreach (Mage::getSingleton('core/session')->getDynamics() as $jsTemplateid => $configurator) {
				$currentJsTemplateid = $this->getJsTemplateId($value);
				$value[$currentJsTemplateid]['dynamics'] = $configurator;
			}
		}

		$result = $this->setData('user_value',serialize($value));
		return $result;
	}

	/**
	 * Validate user input for option
	 *
	 * @throws Mage_Core_Exception
	 * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
	 * @return Mage_Catalog_Model_Product_Option_Type_Default
	 */
	public function validateUserValue($values) {
		$option = $this->getOption();
		$this->setUserValue( $values[$option->getId()] );
		$this->setIsValid(true);
		return $this;
	}

	/**
	 * Prepare option value for cart
	 *
	 * @return mixed Prepared option value
	 */
	public function prepareForCart()
	{
		if ($this->getIsValid() && strlen($this->getUserValue()) > 0 && $this->getUserValue() != 'N;') {
			return $this->getUserValue();
		} else {
			return null;
		}
	}


	public function getFormattedDynamicsValue($key, $value) {
		switch ($key) {
			case "font":
				$font = Mage::getModel("configurator/font")->load($value);
				$typestring = array(0=>'Regular' ,1=>'Italic',2=>'Bold',3=>'Bold-Italic');
				$value = $font->getTitle()." ".$typestring[$font->getFontType()];
				break;
			case "font_size":
				$value = $value."pt";
				break;
			case "font_angle":
				$value = $value." ".Mage::helper('configurator')->__("degree");
				break;
			case "font_color":
				$font = Mage::getModel("configurator/optionfontcolor")->getColorByCode($value);
				$value = $font->getColorTitle();
				break;
			case "font_pos":
				$font = Mage::getModel("configurator/optionfontposition")->getPositionByXY($value);
				$value = $font->getPosTitle();
				break;
			case "text_alignment":
				$aligntring = array(1=>'Left' ,2=>'Center',3=>'Right');
				$value = $aligntring[$value];
				break;
		}
		return $value;
	}

	/**
	 * Return formatted option value for quote option
	 *
	 * @param string $value Prepared for cart option value
	 * @return string
	 */
	public function getFormattedOptionValue($quoteItemOption) {
		if ($quoteItemOption == 'N;') { return ""; }

		$jsTemplateId = $this->getJsTemplateId($quoteItemOption);
		$templateOptions = $this->getTemplateOption($quoteItemOption);
		$session_id =  Mage::getSingleton('core/session')->getSessionId();
		$fileOption = false;
        $product = $this->hasProduct() ? $this->getProduct() : $this->getConfigurationItemOption()->getProduct();

		$html = '<ul>';

        $templateOptionsObj = new Varien_Object();
        $templateOptionsObj->setData('templateOptions', $templateOptions);
        Mage::dispatchEvent('configurator_render_option_value_before',
            array('product' => $product, 'templateOptions' => $templateOptionsObj));
        $templateOptions = $templateOptionsObj->getData('templateOptions');

		foreach ($templateOptions as $templateOption) {

			if (isset($templateOption['option']['template_id']))  {
				$template = Mage::getModel('configurator/template')->load($templateOption['option']['template_id']);

				if ($templateOption['option']['type'] == "file") {
					$fileOption = true;
				} else {
					$price = Mage::app()->getStore()->convertPrice(Mage::helper("configurator")->getPriceInclExclTax($templateOption['value']['price'], $product));
					//$price = ' +' . Mage::helper('core')->formatCurrency($price, false);
					if ($templateOption['option']['is_visible'] == 1) {
						if(trim(strtolower($templateOption['value']['title'])) != 'none') {
							$html .= '<li><strong>' . Mage::helper("configurator")->__($templateOption['option']['title']) . '</strong> ';
							if ($templateOption['option']['type'] != "checkbox") {
								$liValue = Mage::helper("configurator")->__($templateOption['value']['title']);
								$liValue = Mage::helper('configurator')->ieLinebreakFix($liValue);
								$liValue = Mage::helper('configurator')->linebreaksToHtmlBr($liValue);
								$html .= $liValue;
							}
						}
						if ($template->getOptionValuePrice() != 2) { // 2 means show price
							//if ($template->getOptionValuePriceZero() || $price > 0)
								//$html .= $price;
						}

						$html .= '</li>';
						// Check for dynamics value
						$dynamics = $this->getDynamics($jsTemplateId, $templateOption['option']['id'], $quoteItemOption);
						if ($dynamics) {
							$this->setDynamics($jsTemplateId, $templateOption['option']['id'], $dynamics);
							$html .= "<li><strong>" . Mage::helper('configurator')->__('Settings') . "</strong> ";
							$texthtml = "";
							foreach ($dynamics as $key => $value) {
								if ($texthtml) $texthtml .= ", ";
								$value = $this->getFormattedDynamicsValue($key, $value);
								$texthtml .= Mage::helper('configurator')->__($key) . " " . Mage::helper('configurator')->__($value);
							}
							$html .= $texthtml . "</li>";
						}

					}
				}
			}

		}

		if ($fileOption) {
			$data = $this->getData();
			if($data){
				$configurationItem = $data['configuration_item'];
				$configurationItemOption = $data['configuration_item_option'];
				if($configurationItem){
					$configurationItemData = $configurationItem->getData();
					if($configurationItemData){
						$quoteItemId = $configurationItemData['item_id'];
						if($quoteItemId){
							$html = $this->getUploadLiElement($session_id, $jsTemplateId, $quoteItemId, $html);
						}
					}
				}elseif($configurationItemOption){
					$configurationItemData = $configurationItemOption->getData();
					if($configurationItemData){
						$quoteItemId = $configurationItemData['item_id'];
						if($quoteItemId){
							$html = $this->getUploadLiElement($session_id, $jsTemplateId, $quoteItemId, $html);
						}
					}
				}
			}
		}


		$html.= '</ul>';

		return $html;
	}

	public function getPrintableOptionValue($value) {
		if ($value == 'N;') { return ""; }

		$jsTemplateId = $this->getJsTemplateId($value);
		$templateOptions = $this->getTemplateOption($value);
		$session_id =  Mage::getSingleton('core/session')->getSessionId();
		$text = '';
		$fileOption = false;
		$product = $this->getConfigurationItemOption()->getProduct();

        $templateOptionsObj = new Varien_Object();
        $templateOptionsObj->setData('templateOptions', $templateOptions);
        Mage::dispatchEvent('configurator_render_option_value_before',
            array('product' => $product, 'templateOptions' => $templateOptionsObj));
        $templateOptions = $templateOptionsObj->getData('templateOptions');

		foreach ($templateOptions as $templateOption) {
			if (isset($templateOption['option']['template_id'])) {
				$template = Mage::getModel('configurator/template')->load($templateOption['option']['template_id']);

				if ($templateOption['option']['type'] == "file") {
					$fileOption = true;
				} else {
					$price = Mage::app()->getStore()->convertPrice(Mage::helper("configurator")->getPriceInclExclTax($templateOption['value']['price'], $product));
					$price = ' +' . Mage::helper('core')->formatCurrency($price, false);
					if ($templateOption['option']['is_visible'] == 1) {
						$text .= $templateOption['option']['title'];
						if ($templateOption['option']['type'] != "checkbox") {
							$text .= ': ' . Mage::helper("configurator")->__($templateOption['value']['title']);
						}

						if ($template->getOptionValuePrice() != 2) {
							if ($template->getOptionValuePriceZero() || $price > 0)
								$text .= $price;
						}

						// Check for dynamics value
						if ($dynamics = $this->getDynamics($jsTemplateId, $templateOption['option']['id'], $value)) {
							$text .= " (" . Mage::helper('configurator')->__('Settings') . " ";
							$subtext = "";
							foreach ($dynamics as $key => $value) {
								if ($subtext) $subtext .= ", ";
								$value = $this->getFormattedDynamicsValue($key, $value);
								$subtext .= Mage::helper('configurator')->__($key) . " " . Mage::helper('configurator')->__($value);
							}
							$text .= $subtext . ") ";
						}

						$text .= ", ";
					}
				}
			}

		}

		if ($fileOption) {
			$data = $this->getData();
			if($data){
				$configurationItem = $data['configuration_item'];
				$configurationItemOption = $data['configuration_item_option'];
				if($configurationItem){
					$configurationItemData = $configurationItem->getData();
					if($configurationItemData){
						$quoteItemId = $configurationItemData['item_id'];
						if($quoteItemId){
							$text = $this->getUploadTextElement($session_id, $jsTemplateId, $quoteItemId, $text);
						}
					}
				}elseif($configurationItemOption){
					$configurationItemData = $configurationItemOption->getData();
					if($configurationItemData){
						$quoteItemId = $configurationItemData['item_id'];
						if($quoteItemId){
							$text = $this->getUploadTextElement($session_id, $jsTemplateId, $quoteItemId, $text);
						}
					}
				}
			}
		}

		if (strlen($text) > 0) {
			$text = substr($text, 0, strlen($text)-2);
		}
		$text.= '';
		return $text;
	}

	public function getOptionSku($optionValue, $skuDelimiter) {
		$sku = '';

		if( is_array($optionValue) ) {
			$optionValue = serialize(array($this->getJsTemplateOption($optionValue) => array('template' => $optionValue)));
			$templateOptions = $this->getTemplateOption($optionValue);
		} else {
			$templateOptions = $this->getTemplateOption($optionValue);
		}

		if( is_array($templateOptions) && count($templateOptions) > 0) {
			$skus = array();
			foreach($templateOptions as $templateOption) {
				if( !empty($templateOption['value']['sku']) ) {
					$skus[] = $templateOption['value']['sku'];
				}
			}
			if (sizeof($skus) > 0) {
				$sku = implode($skuDelimiter, $skus);
			}
		}

		return '';//$sku;
	}

	/**
	 * @param $session_id
	 * @param $jsTemplateId
	 * @param $quoteItemId
	 * @param $html
	 * @return string
	 */
	private function getUploadLiElement($sessionId, $jsTemplateId, $quoteItemId, $html){
		$uploads = Mage::getModel("configurator/upload")->getCollection();
		$uploads->addFieldToFilter("session_id", array('eq' => $sessionId));
		$uploads->addFieldToFilter("js_template_id", array('eq' => $jsTemplateId));
		$uploads->addFieldToFilter("quote_item_id", $quoteItemId);

		if(count($uploads) > 0){
			foreach($uploads as $upload){
				$texthtml = '';
				$option = Mage::getModel("configurator/option")->load($upload->getOptionId());
				$optionTitle = $option->getTitle();
				$html .= '<li><strong>' . $optionTitle . '</strong> ';
				$texthtml .= basename($upload->getFile());

				$html .= $texthtml;
				$template = Mage::getModel('configurator/template')->load($option->getTemplateId());
				if ($template->getOptionValuePrice() != 2) { // 2 means show price
					$price = ' +'. number_format(Mage::app()->getStore()->convertPrice($option->getPrice()),2,",",""). ' ' . Mage::app()->getStore()->getCurrentCurrency()->getCode();
					if ($template->getOptionValuePriceZero() || $price > 0)
						$html .= $price;
				}
				$html .= "</li>";
			}
			return $html;
		}
		return $html;
	}

	private function getUploadTextElement($sessionId, $jsTemplateId, $quoteItemId, $text){
		$uploads = Mage::getModel("configurator/upload")->getCollection();
		$uploads->addFieldToFilter("session_id", array('eq' => $sessionId));
		$uploads->addFieldToFilter("js_template_id", array('eq' => $jsTemplateId));
		$uploads->addFieldToFilter("quote_item_id", $quoteItemId);

		if(count($uploads) > 0){
			foreach($uploads as $upload){
				$texthtml = '';
				$option = Mage::getModel("configurator/option")->load($upload->getOptionId());
				$optionTitle = $option->getTitle();

				$text.= $optionTitle.': ';
				$texthtml .= basename($upload->getFile());
				$text.= $texthtml;

				$template = Mage::getModel('configurator/template')->load($option->getTemplateId());
				if ($template->getOptionValuePrice() != 2) { // 2 means show price
					$price = ' +'. number_format(Mage::app()->getStore()->convertPrice($option->getPrice()),2,",",""). ' ' . Mage::app()->getStore()->getCurrentCurrency()->getCode();
					if ($template->getOptionValuePriceZero() || $price > 0)
						$text .= $price;
				}
				$text .= ", ";
			}
			return $text;
		}
		return $text;
	}

    /**
     * Returns true in case a product is available (via #getProduct())
     * @return bool
     */
    public function hasProduct() {
        return !is_null($this->_product);
    }
}


