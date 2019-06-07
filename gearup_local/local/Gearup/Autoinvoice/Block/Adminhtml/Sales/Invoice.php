<?php

class Gearup_Autoinvoice_Block_Adminhtml_Sales_Invoice extends Mage_Adminhtml_Block_Sales_Invoice
{

    public function __construct()
    {
        parent::__construct();
        $this->_addButton('cod_report', array(
            'label'     => Mage::helper('adminhtml')->__('COD Report'),
            'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/autoinvoice_codcompare/index') . "')",
            'class'     => 'scalable',
        ), '', 8);
    }
}
