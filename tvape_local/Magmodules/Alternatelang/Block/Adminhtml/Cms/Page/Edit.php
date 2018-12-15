<?php
/**
 * Magmodules.eu - http://www.magmodules.eu - info@magmodules.eu
 * =============================================================
 * NOTICE OF LICENSE [Single domain license]
 * This source file is subject to the EULA that is
 * available through the world-wide-web at:
 * http://www.magmodules.eu/license-agreement/
 * =============================================================
 * @category    Magmodules
 * @package     Magmodules_Alternatelang
 * @author      Magmodules <info@magmodules.eu>
 * @copyright   Copyright (c) 2015 (http://www.magmodules.eu)
 * @license     http://www.magmodules.eu/license-agreement/  
 * =============================================================
 */

class Magmodules_Alternatelang_Block_Adminhtml_Cms_Page_Edit extends Mage_Adminhtml_Block_Cms_Page_Edit {

    public function __construct() {
        parent::__construct();
		if(Mage::getStoreConfig('alternatelang/config/cms_categories')) {		       
			$this->_formScripts[] = " 
				function category_new() {		
					if($('page_alternate_category').value != '-1') {
						$('page_alternate_category_new').up().up().hide();			    
					} else {
						$('page_alternate_category_new').up().up().show();			    
					}
				}
				category_new();
			";
		}	
    }

}
