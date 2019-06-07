<?php
/**
 * Period Container
 */
class Hatimeria_OrderManager_Block_Adminhtml_Period extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'hordermanager';

    public function __construct()
    {
        $this->_controller = 'adminhtml_period';
        $this->_headerText = Mage::helper('hordermanager')->__('Manage Periods');

        parent::__construct();
        $this->removeButton('add');
        $this->addButton('go', array(
            'label'     => Mage::helper('hordermanager')->__('See Periods and Orders'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('hordermanager/adminhtml_order/showall') .'\')',
            'class'     => 'go',
        ));

        if ('supplier' != Mage::getSingleton('admin/session')->getUser()->getUsername()) {
            $this->addButton('load', array(
                'label' => Mage::helper('hordermanager')->__('Initiate Periods'),
                'onclick' => 'setLocation(\'' . $this->getUrl('hordermanager/adminhtml_period/init') . '\')',
                'class' => 'go',
            ));

            $this->addButton('clear', array(
                'label' => Mage::helper('hordermanager')->__('Clear Periods'),
                'onclick' => 'setLocation(\'' . $this->getUrl('hordermanager/adminhtml_period/clear') . '\')',
                'class' => 'go',
            ));
        }
    }
}