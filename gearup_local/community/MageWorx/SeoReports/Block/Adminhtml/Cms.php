<?php
/**
 * MageWorx
 * MageWorx SeoReports Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoReports
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */



class MageWorx_SeoReports_Block_Adminhtml_Cms extends Mage_Adminhtml_Block_Widget_Container
{

    protected function _prepareLayout()
    {
        $this->_addButton(
            'generate',
            array(
            'label'   => Mage::helper('mageworx_seoreports')->__('Generate'),
            'onclick' => "setLocation('{$this->getUrl('*/*/generate')}')",
            'class'   => 'generate'
            )
        );
        if (!Mage::app()->getRequest()->getParam('store')) {
            Mage::app()->getRequest()->setParam('store', Mage::app()->getStore(true)->getId());
        }

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('mageworx_seoreports/adminhtml_cms_grid', 'mageworx.seoreports.cms.grid')
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
