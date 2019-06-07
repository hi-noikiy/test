<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Email_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
   protected function _prepareMassaction()
   {
        // Let the base class do its work
        parent::_prepareMassaction();
        
        $this->getMassactionBlock()->addItem('amlist', array(
            'label'=> $this->__('Email to Customers'),
		    'url'  => $this->getUrl('amemail/adminhtml_index/index', array()) 
        ));

   }
}
