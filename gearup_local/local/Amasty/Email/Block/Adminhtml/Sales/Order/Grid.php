<?php
/**
 * @author Amasty
 */ 
class Amasty_Email_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
   protected function isExtensionActive($extensionName){
        $val = Mage::getConfig()->getNode('modules/' . $extensionName . '/active');
	    return ((string)$val == 'true');
   }
   
   protected function _prepareMassaction()
    {
        // Let the base class do its work
        parent::_prepareMassaction();
        
        if ($this->isExtensionActive('SLandsbek_SimpleOrderExport')){
            $this->getMassactionBlock()->addItem('simpleorderexport',array(
                    'label' => $this->__('Export to .csv file'), 
                    'url'   => $this->getUrl('simpleorderexport/export_order/csvexport')
            ));
        }
        
        if ($this->isExtensionActive('BoutikCircus_DeleteOrders')){
            $this->getMassactionBlock()->addItem('delete_order', array(
                'label'=> Mage::helper('sales')->__('Delete'),
    		    'url'  => $this->getUrl('*/*/deleteorders', array('_current'=>true)),
            ));
        }        
        
        $this->getMassactionBlock()->addItem('amlist', array(
            'label'=> $this->__('Email to Customers'),
		    'url'  => $this->getUrl('amemail/adminhtml_index/index', array()) 
        ));

    }

}
