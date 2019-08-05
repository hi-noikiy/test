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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml\Transaction\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;


/**
 * Grid Grid
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{


    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magestore\Affiliateplus\Helper\Data $helper,
        array $data = array()
    )
    {
        $this->_objectFactory = $objectFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_session = $context->getSession();
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('transaction_data');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General information');
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
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getRegistryModel()->getId()
            ? __("View Transaction '%1'", $this->escapeHtml($this->getRegistryModel()->getAccountName())) : __('Add New Transaction');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());


        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        if ($this->_session->getTransationData()){
            $data = $this->_session->getTransationData();
            $this->_session->setTransationData(null);
        } elseif ($this->getRegistryModel()) {
            $data = $this->getRegistryModel()->getData();
        }
        $store = $this->_getStore();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setTransationData($data);

        $model = $this->getRegistryModel();

        $form->setHtmlIdPrefix('transaction_');

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('transaction_id', 'hidden', ['name' => 'transaction_id']);
        }

        $elements = [];

        $elements['account_email'] = $fieldset->addField(
            'account_email',
            'link',
            [
                'name' => 'account_email',
                'label' => __('Affiliate Account'),
                'title' => __('Affiliate Account'),
                'href'	=> $this->getUrl('*/account/edit', ['_current'=>true, 'account_id' => $data['account_id']]),
            ]
        );

        if (!empty($data['customer_email']))
            $fieldset->addField('customer_email', 'link',
                [
                    'label'	=> __('Customer Email Address'),
                    'href'	=> $this->getUrl('customer/index/edit/',  ['_current'=>true, 'id' => $data['customer_id']]),
                    'title'	=> __('View Customer Details'),
                ]
            );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_field_transaction_form', array('fieldset' => $fieldset, 'form' => $form, 'block' => $this));


        if (!empty($data['order_number']))
            $fieldset->addField('order_number', 'link',
                [
                    'label' => __('Order ID'),
                    'href'	=>  $this->getUrl('sales/order/view/', ['_current'=>true,'order_id' => $data['order_id']]),
                    'title'	=> __('View Order Details'),
                ]

            );

        if (!empty($data['products']))
            $fieldset->addField('products', 'note',
                [
                    'label' => __('Product(s)'),
                    'text'	=> $this->_helper->getBackendProductHtml($data['order_item_ids']),
                ]

            );

        $fieldset->addField('commission', 'note',
            [
                'label' => __('Commission'),
                'text'	=> '<strong>'.$this->_helper->formatPrice($data['commission']).'</strong>',
            ]
        );

        if ($data['percent_plus'] > 0)
            $fieldset->addField('percent_plus', 'note',
                [
                    'label' => __('Additional Commission Percentage'),
                    'text'	=> '<strong>'.sprintf("%.2f",$data['percent_plus']).'%'.'</strong>',
                ]
            );
        
        if ($data['commission_plus'] > 0)
            $fieldset->addField('commission_plus', 'note', array(
                'label' => __('Additional Commission'),
                'text'	=> '<strong>'.$this->_helper->formatPrice($data['commission_plus']).'</strong>',
                'after_element_html'    =>  $this->_helper->renderCurrency($data['commission_plus'], $store)
            ));

        $fieldset->addField('discount', 'note',
            [
                'label' => __('Discount'),
                'text'	=> '<strong>'.$this->_helper->formatPrice($data['discount']).'</strong>',
            ]
        );

        $fieldset->addField('total_amount', 'note',
            [
                'label' => __('Order Subtotal'),
                'text'	=> '<strong>'.$this->_helper->formatPrice($data['total_amount']).'</strong>',
            ]
        );


        $statuses = \Magestore\Affiliateplus\Model\Transaction::getTransactionStatus();
        $elements['status'] = $fieldset->addField(
            'status',
            'note',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'text' => '<b>' . $statuses[$data['status']] . '</b>',
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
