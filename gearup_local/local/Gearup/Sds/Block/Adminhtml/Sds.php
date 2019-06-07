<?php
class Gearup_Sds_Block_Adminhtml_Sds extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_sds';
        $this->_blockGroup         = 'gearup_sds';
        parent::__construct();
        if ($this->getRequest()->getParam('order_filter')) {
            $this->_headerText     = Mage::helper('gearup_sds')->__('Order Report Manager');
        } else if ($this->getRequest()->getParam('inbound_filter')) {
            $this->_headerText     = Mage::helper('gearup_sds')->__('Inbound Filter Manager');
        } else {
            $this->_headerText     = Mage::helper('gearup_sds')->__('DXB Storage Manager');
        }
        $this->_removeButton('add');
        if (!$this->getRequest()->getParam('order_filter') && !$this->getRequest()->getParam('inbound_filter') && !$this->getRequest()->getParam('storage_filter')) {
            $this->_addButton('stock_compare', array(
                'label'     => Mage::helper('adminhtml')->__('Stock compare'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_stockcompare/index') . "')",
                'class'     => 'scalable',
            ), '', 8);
            $this->_addButton('history', array(
                'label'     => Mage::helper('adminhtml')->__('DXB Storage changed history'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_history/index') . "')",
                'class'     => 'scalable',
            ), '', 9);
            $this->_addButton('export_sds', array(
                'label'     => Mage::helper('adminhtml')->__('Export DXB Storage list'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/exportSds') . "')",
                'class'     => 'scalable',
            ), '', 10);
            $this->_addButton('export_sds_low', array(
                'label'     => Mage::helper('adminhtml')->__('Low Stock Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/exportSdsLow') . "')",
                'class'     => 'scalable',
            ), '', 11);
            
            if ($this->getRequest()->getParam('dxbsp_filter')) {
                $this->_addButton('dxbsp', array(
                    'label'     => Mage::helper('adminhtml')->__('Cancel DXBSP filter'),
                    'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index') . "')",
                    'class'     => 'scalable',
                ), '', 7);
            } else {
                $this->_addButton('dxbsp', array(
                    'label'     => Mage::helper('adminhtml')->__('DXBSP filter'),
                    'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('dxbsp_filter' => 1)) . "')",
                    'class'     => 'scalable',
                ), '', 7);
            }
            if ($this->getRequest()->getParam('sdsred_filter')) {
                $this->_addButton('dxbsred', array(
                    'label'     => Mage::helper('adminhtml')->__('Cancel DXBS Red filter'),
                    'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index') . "')",
                    'class'     => 'scalable',
                ), '', 6);
                $this->_addButton('export_dxbs_red', array(
                    'label'     => Mage::helper('adminhtml')->__('Export DXBS Red list'),
                    'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/exportSdsRed') . "')",
                    'class'     => 'scalable',
                ), '', 7);
            } else {
                $this->_addButton('dxbsred', array(
                    'label'     => Mage::helper('adminhtml')->__('DXBS Red filter'),
                    'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('sdsred_filter' => 1)) . "')",
                    'class'     => 'scalable',
                ), '', 6);
            }
        }
        if ($this->getRequest()->getParam('order_filter')) {
            $this->_addButton('cancel_order_report', array(
                'label'     => Mage::helper('adminhtml')->__('Cancel Order Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('reset' => 1)) . "')",
                'class'     => 'scalable',
            ), '', 12);
            $this->_addButton('confirm_order', array(
                'label'     => Mage::helper('adminhtml')->__('Confirm Order'),
                'onclick'   => "saveinbound()",
                'class'     => 'scalable',
            ), '', 15);
        } else if(!$this->getRequest()->getParam('storage_filter')) {
            $this->_addButton('order_report', array(
                'label'     => Mage::helper('adminhtml')->__('Order Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('order_filter' => 1, 'reset' => 1)) . "')",
                'class'     => 'scalable',
            ), '', 13);
        }
        if ($this->getRequest()->getParam('inbound_filter')) {
            $this->_addButton('inbound_report', array(
                'label'     => Mage::helper('adminhtml')->__('Cancel Inbound Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('reset' => 1)) . "')",
                'class'     => 'scalable',
            ), '', 14);
            $this->_addButton('inbound_save', array(
                'label'     => Mage::helper('adminhtml')->__('Save Inbound'),
                'onclick'   => "inboundtostock(2)",
                'class'     => 'scalable',
            ), '', 1);
            $this->_addButton('inbound_stock', array(
                'label'     => Mage::helper('adminhtml')->__('Inbound to Stock/Price'),
                'onclick'   => "inboundtostock(1)",
                'class'     => 'scalable',
            ), '', 2);
            $this->_addButton('export_inbound', array(
                'label'     => Mage::helper('adminhtml')->__('Export Inbound'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/exportinboundnow') . "')",
                'class'     => 'scalable',
            ), '', 3);

            $this->_addButton('last_inbound_report', array(
                'label'     => Mage::helper('adminhtml')->__('Last Inbound Report Generate'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/getLastInboundReport') . "')",
                'class'     => 'scalable',
            ), '', 4);

            $this->_removeButton('order_report');
        } else if(!$this->getRequest()->getParam('storage_filter')) {
            $this->_addButton('inbound_report', array(
                'label'     => Mage::helper('adminhtml')->__('Inbound Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('inbound_filter' => 1, 'reset' => 1)) . "')",
                'class'     => 'scalable',
            ), '', 14);
        }

        if ($this->getRequest()->getParam('storage_filter')) {
            $startDate = $this->getRequest()->getParam('startDate');
            $endDate = $this->getRequest()->getParam('endDate');
            $this->_addButton('export_storage', array(
                'label'     => Mage::helper('adminhtml')->__('Export Storage'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/exportStorage') . "?startDate=".$startDate.'&endDate='.$endDate."')",
                'class'     => 'scalable',
            ), '', 1);
            $this->_addButton('cancel_storage_report', array(
                'label'     => Mage::helper('adminhtml')->__('Cancel Storage Report'),
                'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('reset' => 1)) . "')",
                'class'     => 'scalable',
            ), '', 2);
        }else if(!$this->getRequest()->getParam('inbound_filter') && !$this->getRequest()->getParam('order_filter')){
            $this->_addButton('storage_report', array(
                'label'     => Mage::helper('adminhtml')->__('Storage Report'),
                //'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index', array('storage_filter' => 1, 'reset' => 1)) . "')",
                'onclick'   => 'storagePopup()',
                'class'     => 'scalable',
            ), '', 15);
        }        
    }
}
