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
namespace Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit;

/**
 * Class Tabs
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * Tabs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        /* \Magento\Framework\Event\ManagerInterface $eventManager, */
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        // $this->_eventManager = $eventManager;
        $this->_eventManager = $context->getEventManager();
        $this->_blockFactory = $blockFactory;
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('program_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Program Information'));
    }
    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $id = $this->getRequest()->getParam('program_id');
        $storeId = $this->getRequest()->getParam('store');
        /**
         * add tab
         */

        $this->addTab(
            'form_section',
            [
                'label'   => __('Program Information'),
                'title'   => __('Program Information'),
                'content' => $this->getChildHtml('program_edit_tab_form'),
                'active'  => true
            ]
        );
        $this->addTab(
            'condition',
            [
                'label'   => __('Conditions'),
                'title'   => __('Conditions'),
                'content' => $this->getChildHtml('program_catalog_edit_tab_conditions')
            ]
        );

        $this->addTab(
            'action',
            [
                'label'   => __('Commissions & Discounts'),
                'title'   => __('Commissions & Discounts'),
                'content' => $this->getChildHtml('program_edit_tab_actions')
            ]
        );

        if($id) {

            $this->addTab(
                'transaction_section',
                [
                    'label' => __('Transactions'),
                    'title' => __('Transactions'),
                    'url' => $this->getUrl('*/program/transaction', ['_current' => true, 'program_id' => $id, 'store' => $storeId]),
                    'class' => 'ajax',
                ]
            );
        }
        $this->addTab(
            'account_section',
            [
                'label' => __('Affiliate Accounts'),
                'title' => __('Affiliate Accounts'),
                'url'   => $this->getUrl('*/program/account',['_current'=>true, 'program_id' => $id, 'store' => $storeId]),
                'class' =>'ajax',
            ]
        );
        $this->setActiveTab('form_section');
        return parent::_beforeToHtml();
    }


    /**
     * @param string $tabId
     * @param array|\Magento\Framework\DataObject $tab
     * @param string $afterTabId
     * @throws \Exception
     */
    public function addTabAfter($tabId, $tab, $afterTabId)
    {
        $this->addTab($tabId, $tab);
        $this->_tabs[$tabId]->setAfter($afterTabId);
    }
}
