<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Grid extends TM_ProLabels_Block_Adminhtml_Rules_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('prolabelsSystemGrid');
        $this->setVarNameFilter('pro_system_rules_filter');
    }

    public function getCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::getModel('prolabels/system')
                ->getCollection();
        }

        return $this->_collection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('system_id', array(
            'header'    => Mage::helper('prolabels')->__('ID'),
            'align'     =>'right',
            'width'     => '20px',
            'index'     => 'system_id',
            'type'      => 'number'
        ));
        $this->addColumn('system_label_name', array(
            'header'    => Mage::helper('prolabels')->__('Name'),
            'align'     =>'left',
            'width'     => '450px',
            'index'     => 'system_label_name'
        ));
        $this->addColumn('label_type', array(
            'header'    => Mage::helper('prolabels')->__('System Label'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'rules_id',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('prolabels')->__('On Sale'),
                '2' => Mage::helper('prolabels')->__('In Stock'),
                '3' => Mage::helper('prolabels')->__('Is New')
            )
        ));
        parent::_prepareColumns();
        $this->addColumn('l_status', array(
            'header'    => Mage::helper('prolabels')->__('Enabled'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'l_status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Enabled',
                0 => 'Disabled'
            ),
            'frame_callback' => array($this, 'decorateStatus')
        ));
        $this->removeColumn('rules_id');
        $this->removeColumn('label_name');
        $this->removeColumn('label_status');
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getSystemId()));
    }
}
