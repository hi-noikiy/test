<?php
class Gearup_Autoinvoice_Block_Adminhtml_Codcompare extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_codcompare';
        $this->_blockGroup         = 'gearup_autoinvoice';
        parent::__construct();
        if (Mage::app()->getRequest()->getParam('file')) {
            $this->_headerText         = Mage::helper('adminhtml')->__('Cod Invoice Compare Result');
        } else {
            $this->_headerText         = Mage::helper('adminhtml')->__('Cod Report');
        }
        $this->_removeButton('add');
        if (!Mage::app()->getRequest()->getParam('file')) {
            $this->_addButton('back', array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sales_invoice/index') . "')",
                'class'     => 'back',
            ), '', 9);
        } else {
            $from = Mage::app()->getRequest()->getParam('from');
            $to = Mage::app()->getRequest()->getParam('to');
            $this->_addButton('back', array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/autoinvoice_codcompare/index', array('from'=>$from,'to'=>$to)) . "')",
                'class'     => 'back',
            ), '', 9);
        }
        if (Mage::app()->getRequest()->getParam('file')) {
            $this->_addButton('refresh', array(
                'label'     => Mage::helper('adminhtml')->__('Refresh'),
                'onclick'   => "location.reload()",
                'class'     => 'scalable',
            ), '', 10);
            $this->_addButton('save_invoice', array(
                'label'     => Mage::helper('adminhtml')->__('Change Paid Status'),
                'onclick'   => "changestatus(1)",
                'class'     => 'scalable',
            ), '', 11);
            $this->_addButton('cancel_invoice', array(
                'label'     => Mage::helper('adminhtml')->__('Cancel Invoices'),
                'onclick'   => "changestatus(2)",
                'class'     => 'scalable',
            ), '', 12);
        }

        $this->_addButton('history', array(
            'label'     => Mage::helper('adminhtml')->__('Invoice Changed History'),
            'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/autoinvoice_history/index') . "')",
            'class'     => 'scalable',
        ), '', 13);
    }
}
