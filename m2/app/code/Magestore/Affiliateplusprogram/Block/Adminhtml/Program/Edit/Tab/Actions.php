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


/**
 * Class Actions
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */
class Actions extends Abtractblock
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions Program');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions Program');
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
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('affiliateplusprogram_');

        $fieldset = $form->addFieldset('affiliateplusprogram_actions_commission', ['legend'=>__('Commission')]);

        $elements['affiliate_type']= $fieldset->addField(
            'affiliate_type',
            'select',
            [
                'name' => 'affiliate_type',
                'label' => __('Pay commission'),
                'title' => __('Pay commission'),
                'values'    => $this->_objectManager->get('Magestore\Affiliateplusprogram\Model\System\Config\Source\Type')->toOptionArray(),
            ]
        );

        $elements['commission_type']= $fieldset->addField(
            'commission_type',
            'select',
            [
                'name' => 'commission_type',
                'label' => __('Commission Type'),
                'title' => __('Commission Type'),
                'values'    => $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage')->toOptionArray(),
            ]
        );

        $elements['commission']= $fieldset->addField(
            'commission',
            'text',
            [
                'name' => 'commission',
                'label' => __('Commission Value'),
                'title' => __('Commission Value'),
                'required'  => true,
                'values'    => $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage')->toOptionArray(),
            ]
        );

        $elements['sec_commission']= $fieldset->addField(
            'sec_commission',
            'select',
            [
                'name' => 'sec_commission',
                'label' => __('Use different commission from 2nd order of a Customer'),
                'title' => __('Use different commission from 2nd order of a Customer'),
                'values'    => $this->_yesno->toOptionArray(),
            ]
        );
        $elements['sec_commission_type']= $fieldset->addField(
            'sec_commission_type',
            'select',
            [
                'name' => 'sec_commission_type',
                'label' => __('Commission Type (from 2nd order)'),
                'title' => __('Commission Type (from 2nd order)'),
                'values'    => $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Fixedpercentage')->toOptionArray(),
            ]
        );
        $elements['secondary_commission']= $fieldset->addField(
            'secondary_commission',
            'text',
            [
                'name' => 'secondary_commission',
                'label' => __('Commission Value (from 2nd order)'),
                'title' => __('Commission Value (from 2nd order)'),
            ]
        );
        $this->_eventManager->dispatch('affiliateplusprogram_adminhtml_edit_actions',
            [
                'form'	=> $form,
                'form_data'	=> $model->getData(),
                'fieldset'  => $fieldset,
            ]
        );

        $fieldset = $form->addFieldset('affiliateplusprogram_actions_discount', ['legend'=>__('Discount')]);

        $elements['discount_type']= $fieldset->addField(
            'discount_type',
            'select',
            [
                'name' => 'discount_type',
                'label' => __('Discount Type'),
                'title' => __('Discount Type'),
                'values'    => $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Discounttype')->toOptionArray(),
            ]
        );
        $elements['discount']= $fieldset->addField(
            'discount',
            'text',
            [
                'name' => 'discount',
                'label' => __('Discount Value'),
                'title' => __('Discount Value'),
                'required'  => true,
            ]
        );
        $elements['sec_discount']= $fieldset->addField(
            'sec_discount',
            'select',
            [
                'name' => 'sec_discount',
                'label' => __('Use different discount from 2nd order of a Customer'),
                'title' => __('Use different discount from 2nd order of a Customer'),
                'values'    => $this->_yesno->toOptionArray(),
            ]
        );
        $elements['sec_discount_type']= $fieldset->addField(
            'sec_discount_type',
            'select',
            [
                'name' => 'sec_discount_type',
                'label' => __('Discount Type (from 2nd order)'),
                'title' => __('Discount Type (from 2nd order)'),
                'values'    => $this->_objectManager->get('Magestore\Affiliateplus\Model\System\Config\Source\Discounttype')->toOptionArray(),
            ]
        );
        $elements['secondary_discount']= $fieldset->addField(
            'secondary_discount',
            'text',
            [
                'name' => 'secondary_discount',
                'label' => __('Discount Value (from 2nd order)'),
                'title' => __('Discount Value (from 2nd order)'),
            ]
        );
        $elements['customer_group_ids']= $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name' => 'customer_group_ids',
                'label' => __('Customer Groups Applied'),
                'title' => __('Customer Groups Applied'),
                'values'    => $this->_objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection')->load()
                    ->toOptionArray(),
            ]
        );
        $model->setData('actions', $model->getData('actions_serialized'));

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('sales_rule/promo_quote/newActionHtml/form/affiliateplusprogram_actions_fieldset')
        );
        $this->_eventManager->dispatch('affiliateplusprogram_adminhtml_edit_actions_discount',
            [
                'fieldset'  => $fieldset,
                'form'	=> $form,
                'form_data'	=> $model->getData(),
            ]
        );
        $fieldset = $form->addFieldset(
            'actions_fieldset',
            ['legend' => __('Apply the program only to cart items matching the following conditions (leave blank for all items)')]
        )->setRenderer(
            $renderer
        );

        $elements['actions']= $fieldset->addField(
            'actions',
            'text',
            [
                'name' => 'actions',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'required' => true
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap('affiliateplusprogram_sec_commission', 'use_commission')
            ->addFieldMap('affiliateplusprogram_sec_commission_type', 'commission_type')
            ->addFieldMap('affiliateplusprogram_secondary_commission', 'commission')
            ->addFieldDependence('commission_type', 'use_commission', '1')
            ->addFieldDependence('commission', 'use_commission', '1')
            ->addFieldMap('affiliateplusprogram_sec_discount', 'use_discount')
            ->addFieldMap('affiliateplusprogram_sec_discount_type', 'discount_type')
            ->addFieldMap('affiliateplusprogram_secondary_discount', 'discount')
            ->addFieldDependence('discount_type', 'use_discount', '1')
            ->addFieldDependence('discount', 'use_discount', '1')
        );

        return parent::_prepareForm();
    }

}