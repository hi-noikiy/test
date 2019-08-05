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
namespace Magestore\Affiliateplus\Block\Adminhtml\Account\Edit\Tab;


/**
 * Class Tab GeneralTab
 */
class GeneralTab extends Abtractblock
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Account information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Account information');
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
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->getRegistryModel();
        $storeViewId= $this->getRequest()->getParam('store');
        $attributesInStore = $this->_accountValueCollectionFactory
            ->create()
            ->addFieldToFilter('account_id', $model->getAccountId())
            ->addFieldToFilter('store_id', $storeViewId);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('account_');

        $fieldset = $form->addFieldset('account_fieldset', ['legend' => __('Account Information')]);

        if ($model->getId()) {
            $fieldset->addField('account_id', 'hidden', ['name' => 'account_id']);
        }
        $elements = [];
        $elements['customer_id']= $fieldset->addField(
            'customer_id',
            'hidden',
            [
                'name' => 'customer_id',
                'hidden' => true,
            ]
        );

        $elements['status']= $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => \Magestore\Affiliateplus\Model\Status::getAvailableStatuses(),
//                'disabled' => $disabled,


            ]

        );

        $elements['firstname']= $fieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'required' => true,
            ]
        );

        $elements['lastname']= $fieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'required' => true,
            ]
        );

        $elements['email']= $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email Address'),
                'title' => __('Email Address'),
                'required' => true,
                'class' => 'required-entry validate-email'
            ]
        );

        if(!$model->getAccountId()){
            $elements['password']= $fieldset->addField(
                'password',
                'text',
                [
                    'name' => 'password',
                    'label' => __('Password'),
                    'title' => __('Password'),
                    'note'  => __('Password can be changed by an affiliate.'),
                ]
            );
        }else{
            $elements['customer_account']= $fieldset->addField(
                'customer_account',
                'note',
                [
                    'name' => 'customer_account',
                    'label' => __('Customer Account'),
                    'text' => '<a href="' . $this->getUrl('customer/index/edit', ['id' => $model->getCustomerId()]) . '" title="' . __('Edit Customer') . '">' . $model->getName() . '</a>',
                ]
            );
        }


        $elements['referring_website']= $fieldset->addField(
            'referring_website',
            'text',
            [
                'name' => 'referring_website',
                'label' => __('Referring Website'),
                'title' => __('Referring Website'),
                'note' => __('Enter URL of the website on which affiliates will place your banners and links.'),
            ]
        );
        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_account_info_fieldset', ['form' => $form, 'fieldset' => $fieldset, 'load_data' => $model->getData()]);

        $elements['notification']= $fieldset->addField(
            'notification',
            'select',
            [
                'name' => 'notification',
                'label' => __('Receive notification emails'),
                'required' => false,
                'values' => $this->_yesno->toOptionArray(),
                'value' => 1,
            ]
        );

        if ($model->getData() &&  $model->getData('customer_id')) {
            $form->addValues($model);
        }else{
            $elements['associate_website_id']= $fieldset->addField(
                'associate_website_id',
                'select',
                [
                    'name' => 'associate_website_id',
                    'label' => __('Associate to Website'),
                    'value' => $this->_storeManager->getDefaultStoreView()->getWebsiteId(),
                    'values' => $this->_websiteCustomer->getAllOptions(),
                    'note' => __('For creating a new affiliate account which does not exist in customer database')
                ]
            );
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
