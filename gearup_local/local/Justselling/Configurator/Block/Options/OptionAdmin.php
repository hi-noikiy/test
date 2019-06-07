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

class Justselling_Configurator_Block_Options_OptionAdmin extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option
{
	
 	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('configurator/option.phtml');
    }
	
	protected function _prepareLayout()
    {
        
    	$block = $this->getLayout()->createBlock(
    	    'configurator/options_type_customadmin'
        );
        $block->setProduct( $this->getProduct() );
        
            	
        $this->setChild('custom_option_type',$block);
		
        $parent = parent::_prepareLayout();        
        
        return $parent;
    }
    
	public function getOptionValues()
    {        
    	$this->_values = parent::getOptionValues();
    	
    	foreach($this->_values as $key => $value) {
    		if( $value->getType() == 'configurator' ) { 		

    			$template = Mage::getModel('configurator/template');     			
    			
    			if( ($storeId=$this->getProduct()->getStoreId()) != 0 ) {   

    				$storeTemplateId = $template->getLinkedTemplateId($value->getId(),$storeId);
	    			$templateId = $template->getLinkedTemplateId($value->getId());

	    			//Justselling_Debug::dump($storeId." ".$templateId ." ". $storeTemplateId);
	    			
	    			$disable = ( (isset($storeTemplateId) && isset($templateId)) && $storeTemplateId!=$templateId ) ? true : false;
    				
    				$value['checkboxScopeConfigurator'] = '<br/><input type="checkbox" id="product_option_'.$value['id'].'_template_type_use_default" class="product-option-scope-checkbox" name="product[options]['.$value['id'].'][scope][template_type]" value="1"  '.($disable ? "" : "checked='checked'").' />';
	    			$value['checkboxScopeConfigurator'].= '<label class="normal" for="product_option_'.$value['id'].'_template_type_use_default">Use Default Value</label>';	    			
	    			
	    			$value['scopeConfiguratorDisabled'] = ($disable) ? null : 'disabled';
    				
    				$this->_values[$key]->setData('configurator_template_id', $storeTemplateId );
    			} else {
    				$this->_values[$key]->setData('configurator_template_id', $template->getLinkedTemplateId($value->getId()) );
    			}  			
    			
    			
    		}
    	}
    	
    	return $this->_values;
    }
    
	public function getTemplatesHtml()
    {
        $templates = parent::getTemplatesHtml();
        $templates.= "\n" .$this->getChildHtml('custom_option_type');		
        return $templates;
    }
    
	 public function getTypeSelectHtml()
    {
        $options = Mage::getSingleton('adminhtml/system_config_source_product_options_type')->toOptionArray();
        
   		foreach($options as $key => $option) {   		
   			if( $option['label'] == 'Select' ||  $option['label'] == $this->__("Select") ) {
   				$options[$key]['value'][] = array(
    				"label" => $this->__("Product Configurator"),
    				"value" => "configurator"
    			);
   			}   			
   		}

    	$select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_type',
                'class' => 'select select-product-option-type required-option-select'
            ))
            ->setName($this->getFieldName().'[{{id}}][type]')
            ->setOptions($options);

        return $select->getHtml();
    }
       
}