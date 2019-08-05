<?php
/**
 * Copyright � 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Block\Adminhtml;

class Manifest extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'manifest/manifest.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Create New Manifest'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        //   $this->buttonList->add('add_new', $addButtonProps);

        $addButtonProps = [
            'id' => 'back',
            'label' => __('Back'),
            'class' => 'back',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'onclick' => "setLocation('" . $this->_getBackUrl() . "')",
        ];
        $this->buttonList->add('back', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('CollinsHarper\CanadaPost\Block\Adminhtml\Manifest\Grid', 'manifest.manifest.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Create'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     *
     * @return string
     */
    protected function _getBackUrl()
    {
        return $this->getUrl(
            'cpcanadapost/manifest/index'
        );
    }

    /**
     *
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'cpcanadapost/manifest/edit'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
