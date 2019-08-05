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
class Paymentinfo extends Abtractblock
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Payment information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Payment information');
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
        $model = $this->getRegistryModel()->getData();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('payment_');

        $storeId = $this->getRequest()->getParam('store');
        if ($storeId) {
            $store = $this->_storeModel->create()->load($storeId);
        } else {
            $store = $this->_storeManager->getStore();
        }
        $fieldset = $form->addFieldset('payment_fieldset', ['legend' => __('Payment Information')]);

        if ($this->_configHelper->getSharingConfig('required_paypal')) {

            $fieldset->addField(
                'paypal_email',
                'text',
                [
                    'name' => 'paypal_email',
                    'label' => __('PayPal Email Address'),
                    'title' => __('Name'),
                    'required'  =>true,
                    'class' => 'required-entry validate-email'
                ]
            );
        } else {

            $fieldset->addField(
                'paypal_email',
                'text',
                [
                    'name' => 'paypal_email',
                    'label' => __('PayPal Email Address'),
                    'validate-email' => true,
                ]
            );
        }
        $fieldset->addField(
            'moneybooker_email',
            'text',
            [
                'name' => 'moneybooker_email',
                'label' => __('Moneybooker Email Address'),
                'validate-email' => false,
            ]
        );
        $fieldset->addField(
            'recurring_payment',
            'select',
            [
                'name' => 'recurring_payment',
                'label' => __('Enable Recurring Payment'),
                'values'    => [
                    '1' => __('Yes'),
                    '0' =>  __('No'),
                ]
            ]
        );
        $fieldset->addField(
            'recurring_method',
            'select',
            [
                'name' => 'recurring_method',
                'label' => __('Recurring Payment Method'),
                'values'    => [
                    'paypal' => __('PayPal'),
                    'moneybooker' =>  __('Moneybooker'),
                ]
            ]
        );

        if ($model && isset($model['customer_id']) && $model['customer_id']) {
            if ($this->getClick() && $this->getClick()->getId()) {

                $model['total_clicks'] = $this->getClick()->getData('total_clicks');
                $model['unique_clicks'] = $this->getClick()->getData('unique_clicks');
            }

            $fieldset->addField(
                'total_clicks',
                'label',
                [
                    'label' => __('Total Clicks'),
                    'bold' => true,
                ]
            );
            $fieldset->addField(
                'unique_clicks',
                'label',
                [
                    'label' => __('Unique Clicks'),
                    'bold' => true,
                ]
            );

            if (!isset($model['balance']))
                $model['balance'] = 0;
            $fieldset->addField(
                'balance',
                'note',
                [
                    'label' => __('Balance'),
                    'text' => '<strong>' . $this->_configHelper->formatPrice($model['balance']) . '</strong>',
                ]
            );

            if (!isset($model['total_commission_received']))
                $model['total_commission_received'] = 0;
            $fieldset->addField(
                'total_commission_received',
                'note',
                [
                    'label' => __('Total Commission'),
                    'text' => '<strong>' . $this->_configHelper->formatPrice($model['total_commission_received']) . '</strong>',
                ]
            );

            if (!isset($model['total_paid']))
                $model['total_paid'] = 0;
            $fieldset->addField(
                'total_paid',
                'note',
                [
                    'label' => __('Commission Paid'),
                    'text' => '<strong>' . $this->_configHelper->formatPrice($model['total_paid']) . '</strong>',
                ]
            );
        }



        $form->setValues($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getClick()
    {
        $clickReport = $this->_actionCollectionFactory->create()
            ->addFieldToFilter('type', 2)
            ->addFieldToFilter('account_id', $this->getRequest()->getParam('account_id'));

        $clickReport->getSelect()
            ->columns(
                [
                'total_clicks' => 'SUM(totals)',
                'unique_clicks' => 'SUM(is_unique)'
                ]
            )->group('account_id');
        $clicks = $clickReport->getFirstItem();
        return $clicks;
    }
}
