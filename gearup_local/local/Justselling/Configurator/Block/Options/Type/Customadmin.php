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
 
class Justselling_Configurator_Block_Options_Type_Customadmin extends
    Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Abstract
{
    
	protected $_product;
	
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('configurator/custom.phtml');
    }
    
 	protected function _prepareLayout()
    {
        $this->setChild('option_template_type',
            $this->getLayout()->createBlock('adminhtml/html_select')
                ->setData(array(
                    'id' => 'product_option_{{option_id}}_template_type',
                    'class' => 'select product-option-template-type'
                ))
        );
        
        $this->getChild('option_template_type')->setName('product[options][{{option_id}}][template_type]')
            //->setOptions(array('1'=>'Template Test 1'));
            ->setOptions(Mage::getSingleton('configurator/template')->toOptionArray());

        return parent::_prepareLayout();
    }
    
    public function getTemplateTypeSelectHtml()
    {
    	return $this->getChildHtml('option_template_type');
    }
    
    public function setProduct($product) 
    {
    	$this->_product = $product;
    }
    
    public function getProduct() 
    {
    	return $this->_product;
    }

}
