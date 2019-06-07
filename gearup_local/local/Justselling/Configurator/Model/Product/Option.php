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

/**
 * @method string getIsRequire()
 * @method Justselling_Configurator_Model_Product_Option setIsRequire(string $value)
 * @method string getImageSizeY()
 * @method Justselling_Configurator_Model_Product_Option setImageSizeY(string $value)
 * @method int getProductId()
 * @method Justselling_Configurator_Model_Product_Option setProductId(int $value)
 * @method string getImageSizeX()
 * @method Justselling_Configurator_Model_Product_Option setImageSizeX(string $value)
 * @method string getFileExtension()
 * @method Justselling_Configurator_Model_Product_Option setFileExtension(string $value)
 * @method int getSortOrder()
 * @method Justselling_Configurator_Model_Product_Option setSortOrder(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Product_Option setOptionId(int $value)
 * @method string getSku()
 * @method Justselling_Configurator_Model_Product_Option setSku(string $value)
 * @method string getType()
 * @method Justselling_Configurator_Model_Product_Option setType(string $value)
 * @method int getMaxCharacters()
 * @method Justselling_Configurator_Model_Product_Option setMaxCharacters(int $value)
 */

class Justselling_Configurator_Model_Product_Option extends Mage_Catalog_Model_Product_Option
{
    protected function _construct()
    {
        $this->_init('catalog/product_option');
    }
    
    public function getGroupByType($type = null)
    {
        if (is_null($type)) {
            $type = $this->getType();
        }
        $optionGroupsToTypes = array(
            self::OPTION_TYPE_FIELD => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_AREA => self::OPTION_GROUP_TEXT,
            self::OPTION_TYPE_FILE => self::OPTION_GROUP_FILE,
            self::OPTION_TYPE_DROP_DOWN => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_RADIO => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_CHECKBOX => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_MULTIPLE => self::OPTION_GROUP_SELECT,
            self::OPTION_TYPE_DATE => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_DATE_TIME => self::OPTION_GROUP_DATE,
            self::OPTION_TYPE_TIME => self::OPTION_GROUP_DATE,
            'configurator' => 'configurator'
        );

        return isset($optionGroupsToTypes[$type])?$optionGroupsToTypes[$type]:'';
    }
    
	public function groupFactory($type)
    {
    	// Mage::Log("Justselling_Configurator_Model_Product_Option::groupFactory ".$type);
    	
        $group = $this->getGroupByType($type);       
        
        if (!empty($group)) {
        	
        	if( $group == "configurator" ) {      		
        		return Mage::getModel('configurator/product_option_type_custom');
        	}
        	
            return Mage::getModel('catalog/product_option_type_' . $group);
        }
        Mage::throwException(Mage::helper('catalog')->__('Wrong option type to get group instance.'));
    }

    public function getPrice($flag=false)
    {
        if ($flag && $this->getPriceType() == 'percent') {
            $basePrice = $this->getProduct()->getFinalPrice();
            $price = $basePrice*($this->_getData('price')/100);
            return $price;
        }
        return $this->_getData('price');
    }    
    
	public function saveOptions()
    {
    	foreach ($this->getOptions() as $option) {        
        	
            $this->setData($option)
                ->setData('product_id', $this->getProduct()->getId())
                ->setData('store_id', $this->getProduct()->getStoreId());

            if ($this->getData('option_id') == '0') {
                $this->unsetData('option_id');
            } else {
                $this->setId($this->getData('option_id'));
            }
            $isEdit = (bool)$this->getId()? true:false;

            if ($this->getData('is_delete') == '1') {
                if ($isEdit) {
                    $this->getValueInstance()->deleteValue($this->getId());
                    $this->deletePrices($this->getId());
                    $this->deleteTitles($this->getId());
                    $this->delete();                   
                }
            } else {
                if ($this->getData('previous_type') != '') {
                    $previousType = $this->getData('previous_type');
                    //if previous option has dfferent group from one is came now need to remove all data of previous group
                    if ($this->getGroupByType($previousType) != $this->getGroupByType($this->getData('type'))) {

                        switch ($this->getGroupByType($previousType)) {
                        	case 'configurator':
                        		Mage::Log("saveOptions configurator");
                        		$this->setData('template_type', '');                   		
                        		break;                        	
                        	case self::OPTION_GROUP_SELECT:
                                $this->unsetData('values');
                                if ($isEdit) {
                                    $this->getValueInstance()->deleteValue($this->getId());
                                }
                                break;
                            case self::OPTION_GROUP_FILE:
                                $this->setData('file_extension', '');
                                $this->setData('image_size_x', '0');
                                $this->setData('image_size_y', '0');
                                $this->setData('image_offset_x', '0');
                                $this->setData('image_offset_y', '0');
                                break;
                            case self::OPTION_GROUP_TEXT:
                                $this->setData('max_characters', '0');
                                break;
                            case self::OPTION_GROUP_DATE:
                                break;
                        }
                        if ($this->getGroupByType($this->getData('type')) == self::OPTION_GROUP_SELECT) {
                            $this->setData('sku', '');
                            $this->unsetData('price');
                            $this->unsetData('price_type');
                            if ($isEdit) {
                                $this->deletePrices($this->getId());
                            }
                        }
                    }
                }                
                
                $this->save();                 
                
                if( $option['type'] == 'configurator' ) {   
			
                	//Zend_Debug::dump($option); Zend_Debug::dump($this->_data); exit;
                	
                	
                	/* @ var $templateModel Justselling_Configurator_Model_Template */
                	$templateModel = Mage::getModel('configurator/template');    

                	if( isset($option['scope']) && $option['scope']['template_type'] == "1" ) {
                		
                		if( ($storeId=$this->getProduct()->getStoreId()) != 0 ) {
                			$templateModel->removeTemplateFromProductOption($this->_data['option_id'],$option['id'],$storeId);                		
                		} else {
                			$templateModel->linkTemplateWithProductOption($this->_data['option_id'],$option['template_type']);
                		}                		
                		
                	} else { 
                		$templateModel->linkTemplateWithProductOption($this->_data['option_id'],$option['template_type'],$this->getProduct()->getStoreId());
                	}                	
                }                                  
                
            }
        }
        return $this;
    }
}