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
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment\Review\Edit;

class Form extends \Magestore\Affiliateplus\Block\Adminhtml\Account\Edit\Tab\Abtractblock
{
    /**
     * get registry model.
     *
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getRegistryModel()
    {
        return $this->_coreRegistry->registry('payment_data');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Review your payment and pay');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Review your payment and pay');
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
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('payment_review_');

        $fieldset = $form->addFieldset('review_fieldset', ['legend' => __('Review your payment and pay')]);
        $data = $this->getRequest()->getPostValue();
        $paymentMethod = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');

        if ($paymentId = $this->getRequest()->getParam('payment_id')) {
            $paymentMethod->load($paymentId);
        }
        $paymentMethod = $paymentMethod->addData($data)
            ->getPayment();

        foreach ($data as $key => $value) {
            if ($key == 'form_key') {
                continue;
            }
            if (strpos($key, $paymentMethod->getPaymentCode()) === 0) {
                $paymentMethod->setData(str_replace($paymentMethod->getPaymentCode().'_', '', $key), $value);
            }
            $fieldset->addField($key,'hidden',['name' => $key]);
        }

        $fieldset->addField(
            'show_account_email',
            'note',
            [
                'name' => 'show_account_email',
                'label' => 'To Account',
                'text'  => $data['account_email']
             ]
        );

        $fieldset->addField(
            'show_amount',
            'note',
            [
                'name' => 'show_amount',
                'label' => 'Amount To Transfer',
                'text'  => $this->_storeManager->getStore()->getBaseCurrency()->format($data['amount'])
             ]
        );

        if ($this->getRequest()->getParam('masspayout') == 'true') {
            $data['fee'] = $paymentMethod->getEstimateFee(
                $data['amount'],
                $this->_helperPayment->getConfig('affiliateplus/payment/who_pay_fees', $this->getRequest()->getParam('store'))
            );
        }

        $fieldset->addField('show_fee', 'note',
            [
                'name' => 'show_fee',
                'label' => 'Estimated Fee',
                'text'  => $this->_storeManager->getStore()->getBaseCurrency()->format($data['fee'])
            ]
        );

        $fieldset->addField('payment_info', 'note',
            [
                'name' => 'payment_info',
                'label' => 'Payment Info',
                'text'  => $paymentMethod->getInfoHtml()
            ]
        );

        $form->setValues($data);
        $form->setAction(
            $this->getUrl('*/*/savePayment',
                [
                    'payment_id'    => $this->getRequest()->getParam('payment_id'),
                    'masspayout'    => $this->getRequest()->getParam('masspayout'),
                    'store' => $this->getRequest()->getParam('store')
                ]
            )
        );
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $this->setForm($form);
        return parent::_prepareForm();
    }

}