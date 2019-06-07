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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method string getThumbnailAlt()
 * @method Justselling_Configurator_Model_Value setThumbnailAlt(string $value)
 * @method int getProductId()
 * @method Justselling_Configurator_Model_Value setProductId(int $value)
 * @method int getThumbnailSizeX()
 * @method Justselling_Configurator_Model_Value setThumbnailSizeX(int $value)
 * @method int getThumbnailSizeY()
 * @method Justselling_Configurator_Model_Value setThumbnailSizeY(int $value)
 * @method string getImage()
 * @method Justselling_Configurator_Model_Value setImage(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Value setOptionId(int $value)
 * @method string getSku()
 * @method Justselling_Configurator_Model_Value setSku(string $value)
 * @method string getInfo()
 * @method Justselling_Configurator_Model_Value setInfo(string $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Value setId(int $value)
 * @method string getTitle()
 * @method Justselling_Configurator_Model_Value setTitle(string $value)
 * @method string getThumbnail()
 * @method Justselling_Configurator_Model_Value setThumbnail(string $value)
 * @method int getImageSizeY()
 * @method Justselling_Configurator_Model_Value setImageSizeY(int $value)
 * @method int getImageSizeX()
 * @method Justselling_Configurator_Model_Value setImageSizeX(int $value)
 * @method string getValue()
 * @method Justselling_Configurator_Model_Value setValue(string $value)
 * @method int getSortOrder()
 * @method Justselling_Configurator_Model_Value setSortOrder(int $value)
 * @method int getImageOffsetX()
 * @method Justselling_Configurator_Model_Value setImageOffsetX(int $value)
 * @method int getImageOffsetY()
 * @method Justselling_Configurator_Model_Value setImageOffsetY(int $value)
 */
class Justselling_Configurator_Model_Value extends Mage_Core_Model_Abstract
{
	 /* @var Justselling_Configurator_Model_Template */
	protected $_template;
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/value');
	}
	
	public function getChildOptionValueStatus($optionId) {
		$valuestatusModel = Mage::getModel('configurator/valuechildstatus');		
		$collection = $valuestatusModel->getCollection();
		
		$collection->addFilter('option_value_id',$this->getId());
		$collection->addFilter('option_id',$optionId);
		
		return $collection->getFirstItem();	
	}
	
	public function getBlacklistValue($childOptionId) {
		$blacklistModel = Mage::getModel('configurator/blacklist');		
		$collection = $blacklistModel->getCollection();
		
		$collection->addFilter('option_value_id',$this->getId());
		$collection->addFilter('child_option_value_id',$childOptionId);
		
		return $collection->getFirstItem();	
	}
	
	public function getPrice() {

        $price = $this->price;

        $takePriceFromLinkedProduct = Mage::getStoreConfig('productconfigurator/stock/productprice');
        if($takePriceFromLinkedProduct && $this->getProductId()) {
            /** @var $product Mage_Catalog_Model_Product  */
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            if($this->getValue() && is_numeric($this->getValue())){
                $price = $product->getTierPrice($this->getValue());
            }else{
                $price = $product->getFinalPrice();
            }
        }


		$option = Mage::getModel('configurator/option')->load($this->getOptionId());
		$price = Mage::helper('configurator')->getDiscountPrice($option, $price);
	
		return $price;
	}

	public function getTags() {
		$collection = Mage::getModel("configurator/valuetag")->getCollection();
		$collection->addFieldToFilter("option_value_id",$this->getId());
		$collection->setOrder("id","ASC");

		return $collection;
	}
	
	public function getTagsArray() {
		$collection = $this->getTags();
		$result = array();
		foreach ($collection as $item) {
			$result[] = $item->getTag();
		}
		
		return $result;
	}
	
	public function getTagsString() {
		$collection = $this->getTags();
		$result = "";
		foreach ($collection as $item) {
			if ($result) $result .= " ";
			$result .= $item->getTag();
		}
	
		return $result;
	}

	/**
	 * Checks the currently set image path reference and copies it to the related (new structure) target location. In
	 * case the location has been adjusted it is set in the image reference (data) and - maybe - persisted.<br/>
	 * This method may be called in case of import, or copy of an OptionGroup.
	 * @param $persist bool true if the adjusted image reference should be saved, false (default) if not
	 * @return bool true if at least one has been adjusted, false if not
	 */
	public function adjustImageReferences($persist=false) {
		$hasBeenAdjusted = false;
		$imageFields = array('thumbnail', 'image');
		foreach ($imageFields as $imgField) {
			if ($adjusted = Justselling_Configurator_Model_Export_Processor::adjustAndCopyImageLocation($this, $imgField)) {
				$hasBeenAdjusted = true;
			}
		}
		if ($hasBeenAdjusted && $persist) {
			$this->save();
		}
		return $hasBeenAdjusted;
	}

	/**
	 * Returns the image path (without image file name), relative from 'media' folder.
	 * @param bool $absolute
	 * @param string $field the field to calculate the location path from (i.e. 'option_image')
	 * @return bool|string
	 */
	public function calculateImagePath($absolute=false, $field='') {
		if (!$this->_template || !$this->getOptionId() || !$this->getId()) {
			Js_Log::log("Call of OptionValue::calculateImagePath without valid template reference, optionId or not persisted item!",
				$this, Zend_Log::ERR);
			return false;
		}
		$prefix = '';
		if ($absolute) {
			$prefix = Mage::getBaseDir('media').DS.'configurator'.DS; // configurator only in case of OptionValues!
		}
		return $prefix.$this->_template->getId().DS.$this->getOptionId().DS.$this->getId();
	}

	/**
	 * @param $template Justselling_Configurator_Model_Template
	 */
	public function setTemplate($template) {
		$this->_template = $template;
	}

}