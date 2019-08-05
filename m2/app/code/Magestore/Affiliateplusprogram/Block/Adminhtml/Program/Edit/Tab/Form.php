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
 * Class Form
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */
class Form extends Abtractblock
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Program information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Program information');
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

        $form->setHtmlIdPrefix('program_');

        $fieldset = $form->addFieldset('affiliateplusprogram_form', ['legend' => __('Program Information')]);

        if ($model && $model->getId()) {
            $fieldset->addField('program_id', 'hidden', ['name' => 'program_id']);
        }
        $elements = [];

        $elements['name']= $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Program Name'),
                'title' => __('Program Name'),
                'required' => true,
            ]

        );

        $elements['description']= $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'config' => $this->_wysiwygConfig->getConfig(),
            ]
        );

        $elements['status']= $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => \Magestore\Affiliateplusprogram\Model\Status::getAvailableStatuses(),
            ]
        );

        $elements['show_in_welcome']= $fieldset->addField(
            'show_in_welcome',
            'select',
            [
                'name' => 'show_in_welcome',
                'label' => __('Visible'),
                'title' => __('Visible'),
                'values'  => $this->_yesno->toOptionArray()
            ]
        );

            $elements['autojoin']= $fieldset->addField(
                'autojoin',
                'select',
                [
                    'name' => 'autojoin',
                    'label' => __('Allow auto-join'),
                    'values' => $this->_yesno->toOptionArray()
                ]
            );


        $elements['scope']= $fieldset->addField(
            'scope',
            'select',
            [
                'name' => 'scope',
                'label' => __('Apply to'),
                'title' => __('Apply to'),
                'values' => $this->_scopeModel->toOptionArray(),
                'onchange'  => 'changeScope("#scope")'
            ]
        );
        $value = $this->_objectManager->create('Magento\Customer\Model\ResourceModel\Group\Collection')
            ->addFieldToFilter('customer_group_id', ['gt'=> 0])
            ->load()
            ->toOptionArray();

        $elements['customer_groups']= $fieldset->addField(
            'customer_groups',
            'multiselect',
            [
                'name' => 'customer_groups',
                'label' => __('Customer Groups'),
                'required' => false,
                'values' => $value,
                'after_element_html' =>
                    '<script type="text/javascript">
                    function changeScope(el){
                    require([
                        "jquery"
                    ], function(jQuery){
                        if (jQuery(el).val() != 1)
                        {
                            jQuery("#customer_groups").parent("tr").hide();
                        } else {
                            jQuery("#customer_groups").parent("tr").show();
                        }
                    });

                    }
                     require([
                        "prototype"
                    ], function(){
                        changeScope("#scope");

                    });
			</script>',
            ]
        );
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $elements['valid_from']= $fieldset->addField(
                'valid_from',
                'date',
                [
                    'name' => 'valid_from',
                    'label' => __('From Date'),
                    'date_format' => $dateFormat,
                    'image' => $this->getViewFileUrl('Magestore_Affiliateplusprogram::images/grid-cal.gif'),
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT
                ]
            );

            $elements['valid_to']= $fieldset->addField(
                'valid_to',
                'date',
                [
                    'name' => 'valid_to',
                    'label' => __('To Date'),
                    'date_format' => $dateFormat,
                    'image' => $this->getViewFileUrl('Magestore_Affiliateplusprogram::images/grid-cal.gif'),
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT
                ]
            );

            $elements['priority']= $fieldset->addField(
                'priority',
                'text',
                [
                    'name' => 'priority',
                    'label' => __('Priority'),
                    'class'     => 'validate-zero-or-greater',
                    'note'  => __('The higher the atomic number, the higher the priority.')
                ]
            );
        if($model && $model->getData()){
            $this->_eventManager->dispatch('affiliateplusprogram_adminhtml_edit_form',['fieldset' => $fieldset,'data_form'=>$model->getData()]);
        }

        if ($model && $model->getData('program_id')){

            $elements['created_date']= $fieldset->addField(
                'created_date',
                'note',
                [
                    'name' => 'created_date',
                    'label' => __('Date Created'),
                    'title' => __('Date Created'),
                    'text' => '<strong>'.$this->formatDate($model->getData('created_date'), \IntlDateFormatter::LONG, false).'</strong>'
                ]
            );

            $elements['num_account']= $fieldset->addField(
                'num_account',
                'note',
                [
                    'name' => 'num_account',
                    'label' => __('Number of Affiliate Accounts'),
                    'title' => __('Number of Affiliate Accounts'),
                    'text' => '<strong>'.$model->getData('num_account').'</strong>'
                ]
            );

            $elements['total_sales_amount']= $fieldset->addField(
                'total_sales_amount',
                'note',
                [
                    'name' => 'total_sales_amount',
                    'label' => __('Total Sales Amount'),
                    'title' => __('Total Sales Amount'),
                    'text' => '<strong>'.$this->_storeManager->getStore()->getBaseCurrency()->format($model->getData('total_sales_amount'),array(),false).'</strong>'
                ]
            );
        } else {
            $data['status'] = 1;
            $data['autojoin'] = 1;
            $data['show_in_welcome'] = 1;
            $data['use_coupon'] = 1;
        }
            $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
