<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Abtractblock
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */

class Abtractblock extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magestore\Affiliateplusprogram\Model\Scope
     */
    protected $_scopeModel;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;
    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;
    /**
     * Abtractblock constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab\Context $context,
        /* \Magento\Framework\Registry $registry, */
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magestore\Affiliateplusprogram\Model\Scope $scopeModel,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Rule\Block\Actions $ruleActions,
        array $data = []
    ) {
        /* parent::__construct($context, $registry, $formFactory, $data); */
        parent::__construct($context, $context->getRegistry(), $formFactory, $data);
        $this->_objectManager = $context->getObjectManager();
        $this->_storeManager = $context->getStoreManager();
        $this->_eventManager = $context->getEventManager();
        $this->_yesno = $context->getYesNo();
        $this->_scopeModel = $scopeModel;
        $this->_backendSession = $context->getBackendSession();
        $this->_layout = $context->_getLayout();
        $this->_coreRegistry = $context->getRegistry();
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->_ruleActions = $ruleActions;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout() {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magestore\Affiliateplusprogram\Block\Adminhtml\Form\Program\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Abtract Block');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Abtract Block');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('affiliateplusprogram_data');
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }


}
