<?php

/**
 * Report Container
 *
 * @package GearUp.me
 */
class Mish_Import_Block_Adminhtml_Report_Container extends Mage_Adminhtml_Block_Widget_Container 
{
    protected $_controller = 'adminhtml';
    protected $_blockGroup = 'import';
    protected $_view = 'default';
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->hasData('template')) {
            $this->setTemplate('import/report/container.phtml');
        }

        $this->_addButton('back', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() . '\')',
            'class'     => 'back',
        ), -1);
    }
    
    protected function _prepareLayout()
    {
        if ($this->_blockGroup && $this->_controller && $this->_view) {
            $this->setChild('container_content', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_view));
        }
        return parent::_prepareLayout();
    }
    
    public function getContainerContentHtml()
    {
        return $this->getChildHtml('container_content');
    }

}