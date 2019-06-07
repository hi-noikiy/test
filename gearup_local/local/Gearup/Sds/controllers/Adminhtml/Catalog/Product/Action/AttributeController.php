<?php
include_once("Mage/Adminhtml/controllers/Catalog/Product/Action/AttributeController.php");
class Gearup_Sds_Adminhtml_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Catalog_Product_Action_AttributeController
{

    public function saveattriAction()
    {
        parent::saveAction();
        if ($this->getRequest()->getParam('sds')) {
            $this->_redirect('*/sds_sds/', array('store'=>$this->_getHelper()->getSelectedStoreId()));
        } else {
            $this->_redirect('*/catalog_product/', array('store'=>$this->_getHelper()->getSelectedStoreId())); 
        }
    }

}
