<?php
class Gearup_Shippingffdx_Block_Adminhtml_Reportcompare extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_reportcompare';
        $this->_blockGroup         = 'gearup_shippingffdx';
        parent::__construct();
        if (Mage::app()->getRequest()->getParam('file')) {
            $desti = base64_decode(Mage::app()->getRequest()->getParam('desti'));
            $this->_headerText     = Mage::helper('adminhtml')->__('Report Shippingffdx Compare Result') . ' (' . $desti . ')';
        } else {
            $this->_headerText     = Mage::helper('adminhtml')->__('Shippingffdx Report');
        }
        $this->_removeButton('add');
        if (!Mage::app()->getRequest()->getParam('file')) {
            $this->_addButton('back', array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('ffdxshippingbox/adminhtml_tracking/index') . "')",
                'class'     => 'back',
            ), '', 9);
        } else {
            $from = Mage::app()->getRequest()->getParam('from');
            $to = Mage::app()->getRequest()->getParam('to');
            $desti = Mage::app()->getRequest()->getParam('desti');
            $this->_addButton('back', array(
                'label'     => Mage::helper('adminhtml')->__('Back'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/*/index', array('from'=>$from,'to'=>$to,'desti'=>$desti)) . "')",
                'class'     => 'back',
            ), '', 9);

            $this->_addButton('changed', array(
                'label'     => Mage::helper('adminhtml')->__('Changed'),
                'onclick'   => "changestatus(1)",
                'class'     => 'scalable',
            ), '', 11);
        }
        if (Mage::app()->getRequest()->getParam('file')) {
            $this->_addButton('refresh', array(
                'label'     => Mage::helper('adminhtml')->__('Refresh'),
                'onclick'   => "location.reload()",
                'class'     => 'scalable',
            ), '', 10);
        }

        $this->_addButton('history', array(
            'label'     => Mage::helper('adminhtml')->__('Track(s) Changed History'),
            'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/reportffdxhistory/index') . "')",
            'class'     => 'scalable',
        ), '', 13);
    }
}
