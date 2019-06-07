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

class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget
{
	protected $_data = null;
	
	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('configurator/template/options.phtml');    

        $this->_data = Mage::registry("configurator_data")->getData();;
	}
	
	protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Option'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_option'
                ))
        );

        $this->setChild('options_box',
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_options_option')
        );
        
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Option'),
                    'class' => 'delete delete-template-option '
                ))
        );
        
        $this->setChild('copy_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Copy Option'),
                    'class' => 'add copy copy-template-option '
                ))
        );
        
        $this->setChild('add_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Row'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_row'
                ))
        );
        
        $this->setChild('delete_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete this Row'),
                    'class' => 'delete delete-template-option-row '
                ))
        );
        
        $this->setChild('deny_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Settings'),
                    'class' => 'details details-template-option-values '
                ))
        );

        $this->setChild('edit_option_value_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Edit'),
                    'class' => 'edit-template-option-value '
                ))
        );

        $this->setChild('save_option_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Save'),
                    'class' => 'save-template-option '
                ))
        );

        $this->setChild('cancel_option_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Cancel'),
                    'class' => 'cancel-template-option '
                ))
        );

        $this->setChild('save_option_value_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Save'),
                    'class' => 'save-template-option-value '
                ))
        );

        $this->setChild('cancel_option_value_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Cancel'),
                    'class' => 'cancel-template-option-value '
                ))
        );

        $this->setChild('option_details',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Edit'),
                    'class' => 'edit edit-template-option '
                ))
        );
        
        $this->setChild('price_table',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Price Table'),
                    'class' => 'details pricetable-template-pricelist '
                ))
        );
        
        $this->setChild('pricelist_add_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Row'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_pricelist_row'
                ))
        );
        
        $this->setChild('pricelist_delete_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete this Row'),
                    'class' => 'delete delete-template-pricelist-row '
                ))
        );
        
        $this->setChild('pricelistvalue_add_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add New Row'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_pricelistvalue_row'
                ))
        );
        
        $this->setChild('pricelistvalue_delete_row',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete this Row'),
                    'class' => 'delete delete-template-pricelistvalue-row '
                ))
        );
        
        $this->setChild('textimage_configure_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Configure Frontend'),
                    'class' => 'details textimage_configure_button ',
                    'onclick' => " jQuery('#textimage_overlay').overlay().load();"
                ))
        );
        
        $this->setChild('textimage_configure_save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Save Font Configuration'),
                    'class' => 'save textimage_configure_save_button ',
                    'onclick' => ""
                ))
        );
        
        $this->setChild('export_matrix_button',
        		$this->getLayout()->createBlock('adminhtml/widget_button')
        		->setData(array(
        				'label' => Mage::helper('catalog')->__('Export Matrix'),
        				'class' => 'details export_matrix_button',
        		))
        );       
        
        return parent::_prepareLayout();
    }
    
    
    public function getTextimageButtonHtml()
    {
        return $this->getChildHtml('textimage_configure_button');
    }
    
    public function getTextimageSaveButtonHtml()
    {
        return $this->getChildHtml('textimage_configure_save_button');
    }
    
	public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

	public function getSaveOptionButtonHtml()
    {
        return $this->getChildHtml('save_option_button');
    }

	public function getCancelOptionButtonHtml()
    {
        return $this->getChildHtml('cancel_option_button');
    }

	public function getCopyButtonHtml()
    {
        return $this->getChildHtml('copy_button');
    }
    
	public function getDeleteRowHtml()
    {
        return $this->getChildHtml('delete_row');
    }
    
	public function getPricelistAddButtonHtml()
    {
        return $this->getChildHtml('pricelist_add_row');
    }
    
	public function getPricelistDeleteRowHtml()
    {
        return $this->getChildHtml('pricelist_delete_row');
    }
    
	public function getPricelistvalueAddButtonHtml()
    {
        return $this->getChildHtml('pricelistvalue_add_row');
    }
    
	public function getPricelistvalueDeleteRowHtml()
    {
        return $this->getChildHtml('pricelistvalue_delete_row');
    }
    
	public function getDenyButtonHtml()
    {
    	return $this->getChildHtml('deny_button');
    }

	public function getSaveOptionValueButtonHtml()
    {
    	return $this->getChildHtml('save_option_value_button');
    }

	public function getCancelOptionValueButtonHtml()
    {
    	return $this->getChildHtml('cancel_option_value_button');
    }

	public function getEdiOptionValueButtonHtml()
    {
    	return $this->getChildHtml('edit_option_value_button');
    }

	public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
	public function getRowButtonHtml()
    {
        return $this->getChildHtml('add_row');
    }
    
	public function getOptionDetailButtonHtml()
    {
        return $this->getChildHtml('option_details');
    }
    
	public function getPriceTableButtonHtml()
    {
        return $this->getChildHtml('price_table');
    }
    
    public function getMatrixExportButtonHtml()
    {
    	return $this->getChildHtml('export_matrix_button');
    }
    
    public function getTemplateData()
    {
    	return $this->_data;
    }
    
    public function getTemplateId()
    {    	
    	if( isset($this->_data['id']) ) return $this->_data['id'];    	
    	return null;
    }    
    
    public function getOptions()
    {
    	return Mage::getSingleton("configurator/option")->getTemplateOptions($this->getTemplateId());    	
    }

    public function getOptionValues()
    {   			
		if( is_null($this->getTemplateId()) ) return null;

		$items = Mage::helper('configurator')->getOptionValueByIdOrTemplateId(null, $this->_data['id']);

		return $items;
    }

    public  function  getBaseFrontendUrl(){
        (empty($_SERVER['HTTPS'])) ? $ssl = array('_secure' => false) : $ssl = array('_secure' => true);
        $baseUrl = Mage::getUrl('', $ssl);

        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
            $_storeName = Mage::app()->getStore($_eachStoreId)->getName();
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $baseUrl = Mage::app()->getStore($_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
            break;
        }

		// make sure that the frontend url has the same protocol as backend url for cross domain javacript issues
		if(!empty($_SERVER['HTTPS']) && strpos($baseUrl, 'http:') !== FALSE){
			$baseUrl = str_replace("http:","https:",$baseUrl);
		}else if(empty($_SERVER['HTTPS']) && strpos($baseUrl, 'https:') !== FALSE){
			$baseUrl = str_replace("https:","http:",$baseUrl);
		}

        return $baseUrl;

    }
    
    public function getOptionValueTree(&$items)
    {
    	//Zend_Debug::dump($items);
    	for($i=0; $i<count($items); $i++) { 
    		$items[$i]['children'] = $this->getOptionValueChildren($items[$i], $items); }
    }
    
    public function getOptionValueChildren($node,$items)
    {
    	$children = array();
    	for($i=0; $i<count($items); $i++) {
    		if( $node['id'] == $items[$i]['parent_id'] ) {
    			$children[] = &$items[$i];
    			$children = array_merge($children, $this->getOptionValueChildren($items[$i], $items) );
    		}
    	}
    	return $children;	
    }
    
	public function getLastOptionId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionTable = Mage::getSingleton("core/resource")->getTableName('configurator/option');
	
		$select = $connection->select()
			->from(array("co" => $optionTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}
    
	public function getLastOptionValueId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
	
		$select = $connection->select()
			->from(array("cov" => $optionValueTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}
	
	public function getLastPricelistId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $pricelistTable = Mage::getSingleton("core/resource")->getTableName('configurator/pricelist');
	
		$select = $connection->select()
			->from(array("cp" => $pricelistTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}	
	
	public function getLastValuePricelistId()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $pricelistValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/pricelist_value');
	
		$select = $connection->select()
			->from(array("cpv" => $pricelistValueTable),array("MAX(id)"));
			
		$id = $connection->fetchOne($select);
		
		if($id) return $id;
		
		return 0;
	}
    
    public static function getFieldName()
    {
    	return "template[options]";
    }
    
    public static function getFieldId()
    {
    	return "template_option";
    }
    
	public function getParentSelectHtml()
    {
    	//Zend_Debug::dump( $this->getOptions()->toOptionArray() );  
    	
    	$options = Mage::getSingleton("configurator/option")->toOptionArrayWithId($this->getTemplateId());
    	
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_parent',
                'class' => 'select select-product-option-parent_id required-option-select'
            ))
            ->setName($this->getFieldName().'[${id}][parent_id]')
            ->setOptions($options);           
           
        return $select->getHtml();
    }
    
	public function getFontSelectHtml()
    {
    	//Zend_Debug::dump( $this->getOptions()->toOptionArray() );  
    	
    	$options = Mage::getSingleton("configurator/font")->toFontArray();
    	
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_font',
                'class' => 'select select-product-option-font required-option-select'
            ))
            ->setName($this->getFieldName().'[${id}][font]')
            ->setOptions($options);           
           
        return $select->getHtml();
    }   
    
	public function getFontMultiSelectHtml()
    {
    	//Zend_Debug::dump( $this->getOptions()->toOptionArray() );  
    	
    	$fonts = Mage::getSingleton("configurator/font")->toFontArray();
    	
		$html = "<select id=\"all-fonts\" class=\"multiselect\" multiple=\"multiple\">";
		foreach ($fonts as $id => $title) {
			$html .= "<option value=\"".$id."\">".$title."</option>";
		}
		$html .= "</select>";
         
        return $html;
    }       
    
    public function getFontMultiSelectPerOptionHtml()
    {
		$html = "<select  id=\"selected-fonts\" class=\"multiselect\"  multiple=\"multiple\">";
		$html .= "</select>";
         
        return $html;
    }        

	public function getTypesArray(){
		$select = array("select" => "Select", "selectimage" => "Select with Image", "overlayimage" => "Overlay with Images", "radiobuttons" => "Radio Buttons", "checkbox" => "Checkbox" ,"listimage" => "List with Image");
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $select["file"] = "File Upload";
		$value = array("combi" => "Combi","selectcombi" => "Select Combi","overlayimagecombi" => "Overlayimage Combi", "listimagecombi" => "Listimage Combi");
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value["expression"] = "Expression";
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value["matrixvalue"] = "Matrixvalue";
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value["http"] = "Webservice";
		$text = array("text" => "Text","area" => "Area","static" => "Static");
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("U"))) $text["textimage"] = "Text2Image";
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $text["productattribute"] = "Product Attribute";
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $text["date"] = "Date";
		$types = array(
			array("label" => $this->__("-- Please Select --"),"value" => ""),
			array(
				"label" => "Select",
				"value" => $select
			),
			array(
				"label" => "Text",
				"value" => $text
			),
			array(
				"label" => "Combi",
				"value" => $value
			),
			array(
				"label" => "Extensions",
				"value" => array()
			)
		);

		/* Allow extension to add new frontend types */
		$data = new Varien_Object();
		$data->setTypes($types);
		Mage::dispatchEvent('prodconf_build_type_array_after', array('types' => $data));
		$types = $data->getTypes();
		return $types;
	}

	public function getTypesJson(){
		$types = $this->getTypesArray();

		$jsonTypesArray = array();
		foreach($types as $typeArray){
			$typesValues = $typeArray['value'];
			if(is_array($typesValues)){
				foreach($typesValues as $type => $typeLabel){
					$jsonTypesArray[$type] = $typeLabel;
				}
			}
		}

		return $jsonTypesArray;
	}

	public function getTypeSelectHtml()
    {
    	$types = $this->getTypesArray();
            	
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_type',
                'class' => 'select select-product-option-type required-option-select validate-select'
            ))
            ->setName($this->getFieldName().'[${id}][type]')
            ->setOptions($types);

        return $select->getHtml();
    }
    
	public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_is_require',
                'class' => 'select select-product-option-is_require'
            ))
            ->setName($this->getFieldName().'[${id}][is_require]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    public function getCheckboxDefaultValueSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_default_value',
                'class' => 'select select-product-option-default_value'
            ))
            ->setName($this->getFieldName().'[${id}][default_value]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    public function getListimageHoverSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
    	->setData(array(
    			'id' => $this->getFieldId().'_${id}_listimage_hover',
    			'class' => 'select select-product-listimage-hover'
    	))
    	->setName($this->getFieldName().'[${id}][listimage_hover]')
    	->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());
    
    	return $select->getHtml();
    }  

    public function getListimageStyleSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
    	->setData(array(
    			'id' => $this->getFieldId().'_${id}_listimage_style',
    			'class' => 'select select-product-listimage-style'
    	))
    	->setName($this->getFieldName().'[${id}][listimage_style]')
    	->setOptions(array('0' => Mage::helper('configurator')->__("Image only"), '1' => Mage::helper('configurator')->__("Image with label")));
    
    	return $select->getHtml();
    }

    public function getTextImageAligmentSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
    	->setData(array(
    			'id' => $this->getFieldId().'_${id}_text_alignment',
    			'class' => 'select select-text_alignment'
    	))
    	->setName($this->getFieldName().'[${id}][text_alignment]')
    	->setOptions(array('1' => Mage::helper('configurator')->__("Left"), '2' => Mage::helper('configurator')->__("Center"), '3' => Mage::helper('configurator')->__("Right")));

    	return $select->getHtml();
    }

	public function getValueSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_value',
                'class' => 'select select-product-option-value'
            ))
            ->setName($this->getFieldName().'[${id}][value]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }
    
	public function getPlaceholderSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_placeholder',
                'class' => 'select select-product-option-placeholder'
            ))
            ->setName($this->getFieldName().'[${id}][placeholder]')
            ->setOptions(array('0' => Mage::helper('configurator')->__("Default Value"), '1' => Mage::helper('configurator')->__("Placeholder")));

        return $select->getHtml();
    }
    
	public function getUploadTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_upload_type',
                'class' => 'select select-product-option-upload_type'
            ))
            ->setName($this->getFieldName().'[${id}][upload_type]')
            ->setOptions(array('0' => Mage::helper('configurator')->__("One File"), '1' => Mage::helper('configurator')->__("Multiple Files")));

        return $select->getHtml();
    }

    public function getTextValidateSelectHtml()
    {
    	 
    	$options = array(
    				"0"=>Mage::helper('configurator')->__("no restrictions"), 
    				"validate-number"=>Mage::helper('configurator')->__("number"), 
    				"validate-digits"=>Mage::helper('configurator')->__("digits"),
    				"validate-alpha"=> Mage::helper('configurator')->__("letters"),
    				"validate-code"=>Mage::helper('configurator')->__("code"),
    				"validate-alphanum"=>Mage::helper('configurator')->__("letters and numbers"),
    				"validate-zip"=>Mage::helper('configurator')->__("zip code"),
    				"validate-phoneLax"=>Mage::helper('configurator')->__("phone"),
    				"validate-not-negative-number"=>Mage::helper('configurator')->__("number not negative"),
    				"validate-greater-than-zero"=>Mage::helper('configurator')->__("number greater zero"),
    				"validate-zero-or-greater"=>Mage::helper('configurator')->__("number greater or zero"),
    				"validate-data" => Mage::helper('configurator')->__("data")
    			);
    	 
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
    	->setData(array(
    			'id' => $this->getFieldId().'_${id}_text_validate',
    			'class' => 'select select-product-option-text_validate'
    	))
    	->setName($this->getFieldName().'[${id}][text_validate]')
    	->setOptions($options);
    	 
    	return $select->getHtml();
    }   
    
    public function getApplyDiscountSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_apply_discount',
                'class' => 'select select-product-option-apply_discount'
            ))
            ->setName($this->getFieldName().'[${id}][apply_discount]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }
    
    public function getVisibleSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_is_visible',
                'class' => 'select select-product-option-is_visible'
            ))
            ->setName($this->getFieldName().'[${id}][is_visible]')
            ->setOptions(array(
                '0' => Mage::helper('catalog')->__('No'),
                '1' => Mage::helper('catalog')->__('Yes'),
                '2' => Mage::helper('catalog')->__('Frontend')
            ));

        return $select->getHtml();
    }
    
    public function getGroupSelectHtml()
    {
    	$grouparray = array("0" => Mage::helper('configurator')->__('no group'));
		$groups = Mage::getModel("configurator/optiongroup")->getCollection();
		$groups->addFilter('template_id',$this->getTemplateId());
		foreach ($groups as $group) {
			$grouparray[$group->getId()] = $group->getTitle();
		}
    
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_option_group',
                'class' => 'select select-product-option-option_group'
            ))
            ->setName($this->getFieldName().'[${id}][option_group]')
            ->setOptions($grouparray);

        return $select->getHtml();
    }    
    
	public function getOperatorSelectHtml()
    {
    	$value = array('+' => '+','*' => '*');
    	if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value['string'] = 'String';
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_operator',
                'class' => 'select select-temlate-option-operator'
            ))
            ->setName($this->getFieldName().'[${id}][operator]')
            ->setOptions($value);

        return $select->getHtml();
    }

	public function getOperatorSelectCombiHtml()
    {
    	$value = array('+' => '+','*' => '*');
    	if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value['string'] = 'String';
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) $value['expression'] = 'Expression';
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_operator',
                'class' => 'select select-temlate-option-operator'
            ))
            ->setName($this->getFieldName().'[${id}][operator]')
            ->setOptions($value);

        return $select->getHtml();
    }

	public function getOperatorValuePriceSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_operator_value_price',
                'class' => 'select select-temlate-option-operator-value-price'
            ))
            ->setName($this->getFieldName().'[${id}][operator_value_price]')
            ->setOptions(array(
            	'none' => $this->__('None'),'*' => Mage::helper('catalog')->__('Value * Price')));

        return $select->getHtml();
    }
    
    public function getMatrixMatchSelectHtml($dimension)
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
    	->setData(array(
    			'id' => $this->getFieldId().'_${id}_matrix_operator_'.$dimension,
    			'class' => 'select select-product-option-matrix_operator_'.$dimension.' required-option-select validate-select'
    	))
    	->setName($this->getFieldName().'[${id}][matrix_operator_'.$dimension.']')
    	->setOptions(array(
    			'match' => Mage::helper('catalog')->__('Match'),
    			'number higher' => Mage::helper('catalog')->__('Higher Number'),
    			'number lower' => Mage::helper('catalog')->__('Lower Number')
    	));

    	return $select->getHtml();
    }    
       
	public function getPricelistOperatorSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${option_id}_pricelist_${id}_operator',
                'class' => 'select select-template-option-pricelist-operator'
            ))
            ->setName($this->getFieldName().'[${option_id}][pricelist][${id}][operator]')
            ->setOptions(array(
            	'>' => Mage::helper('catalog')->__('Values').' >',
            	'<' => Mage::helper('catalog')->__('Values').' <',
            	'==' => Mage::helper('catalog')->__('Values').' =',
            	'>=' => Mage::helper('catalog')->__('Values').' >=',
            	'<=' => Mage::helper('catalog')->__('Values').' <='
            ));

        return $select->getHtml();
    }
    
	public function getPricelistValueOperatorSelectHtml()
    {
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${option_id}_values_${value_id}_pricelist_${id}_operator',
                'class' => 'select select-template-option-pricelistvalue-operator'
            ))
            ->setName($this->getFieldName().'[${option_id}][values][${value_id}][pricelist][${id}][operator]')
            ->setOptions(array(
            	'>' => Mage::helper('catalog')->__('Values').' >',
            	'<' => Mage::helper('catalog')->__('Values').' <',
            	'==' => Mage::helper('catalog')->__('Values').' =',
            	'>=' => Mage::helper('catalog')->__('Values').' >=',
            	'<=' => Mage::helper('catalog')->__('Values').' <='
            ));

        return $select->getHtml();
    }
    
    public function getProductSelectHtml()
    {
    	/*
    	$productCollection = Mage::getModel("catalog/product")->getCollection();
    	$productCollection->addAttributeToSelect('name');     
    	$options = array(null=>' ');
    	foreach($productCollection->getItems() as $item) {
    		$options[$item->getId()] = $item->getName();
    	}

    	
    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_${id}_product_id',
                'class' => 'select select-product-option-product_id'
            ))
            ->setName($this->getFieldName().'[${id}][product_id]')
            ->setOptions($options);           
           
        return $select->getHtml();
        */
    }
    
}
