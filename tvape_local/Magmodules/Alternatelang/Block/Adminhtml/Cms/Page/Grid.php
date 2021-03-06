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

class Magmodules_Alternatelang_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid {
   
	protected function _prepareColumns(){
		if(Mage::getStoreConfig('alternatelang/config/cms_categories')) {		       
			$collection = Mage::getModel('cms/page')->getCollection()->distinct(true)->addFieldToSelect('alternate_category')->setOrder('title','ASC');
			$collection->setFirstStoreFlag(true);
			$alternate_category_options = '';
		
			foreach($collection as $option):
				if($option->getAlternateCategory()):
					$alternate_category_options[$option->getAlternateCategory()] = $option->getAlternateCategory();
				endif;
			endforeach;
		
			$this->addColumnAfter('alternate_category', array(
				'header'    => Mage::helper('alternatelang')->__('Category'),
				'index'     => 'alternate_category',
				'type'		=> 'options',
				'options'	=> $alternate_category_options,
			),'update_time');
		}	
        return parent::_prepareColumns();
    }
    
}