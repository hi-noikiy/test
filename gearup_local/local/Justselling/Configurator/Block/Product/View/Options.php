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
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
	public function __construct()
    {
        parent::__construct();
        
        //Zend_Debug::dump("Options rewrite");
        
        $this->addOptionRenderer(
            'configurator',
            'configurator/product_view_options_type_custom',
            'configurator/custom.phtml'
        );
    }
        
	public function getGroupOfOption($type)
    {
       	$group = Mage::getModel('configurator/product_option')->getGroupByType($type);

        return $group == '' ? 'default' : $group;
    }
    
}