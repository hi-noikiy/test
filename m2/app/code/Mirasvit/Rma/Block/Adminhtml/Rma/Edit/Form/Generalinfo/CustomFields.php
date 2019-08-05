<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\Generalinfo;

use Magento\Framework\Exception\NoSuchEntityException;

class CustomFields extends \Magento\Backend\Block\Template
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $rmaField,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig,
        \Mirasvit\Rma\Model\ResourceModel\Address\Collection $addressCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\CreditMemoRepositoryInterface $creditMemoRepository,
        \Mirasvit\Rma\Helper\Customer\Url $rmaCustomerUrl,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->rmaField             = $rmaField;
        $this->rmaManagement        = $rmaManagement;
        $this->rmaConfig            = $rmaConfig;
        $this->addressCollection    = $addressCollection;
        $this->orderRepository      = $orderRepository;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->rmaCustomerUrl       = $rmaCustomerUrl;
        $this->formFactory          = $formFactory;

        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Rma\Api\Data\RmaInterface           $rma
     * @return void
     */
    public function getReturnAddress($fieldset, $rma)
    {
        $defaultAddress = $this->rmaConfig->getReturnAddress($rma->getStoreId());
        $fieldset->addField('return_address', 'select', [
            'label'  => __('Return Address'),
            'name'   => 'return_address',
            'value'  => $this->rmaManagement->getReturnAddressHtml($rma),
            'values' => $this->addressCollection->toOptionArray(true, $defaultAddress)
        ]);
        $fieldset->addField('return_address_text', 'note', [
            'label' => '',
            'name'  => 'return_address_text',
            'text'  => nl2br($this->rmaManagement->getReturnAddressHtml($rma)),
        ]);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool|\Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFieldForm(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('field_fieldset', ['legend' => __('Additional Information')]);
        $collection = $this->rmaField->getStaffCollection();
        if (!$collection) {
            return false;
        }
        foreach ($collection as $field) {
            $fieldset->addField(
                $field->getCode(),
                $field->getType(),
                $this->rmaField->getInputParams($field, true, $rma)
            );
        }

        return $form;
    }


    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return void
     */
    public function addCustomerLink($fieldset, \Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        if ($rma->getCustomerId()) {
            $fieldset->addField('customer', 'link', [
                'label' => __('Customer'),
                'name'  => 'customer',
                'value' => $this->rmaManagement->getFullName($rma),
                'href'  => $this->rmaCustomerUrl->getBackendUrl($rma->getCustomerId()),
            ]);
        } else {
            $fieldset->addField('customer', 'label', [
                'label' => __('Customer'),
                'name'  => 'customer',
                'value' => $this->rmaManagement->getFullName($rma)
                    . ($rma->getIsGift() ? ' ' . __('(received a gift)') : ''),
            ]);
        }

    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return void
     */
    public function addExchangeOrders($fieldset, \Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        if ($rma->getExchangeOrderIds()) {
            $links = [];
            foreach ($rma->getExchangeOrderIds() as $id) {
                try {
                    $exchangeOrder = $this->orderRepository->get($id);
                    $links[] = "<a href='" . $this->getUrl(
                            'sales/order/view',
                            ['order_id' => $id]
                        ) . "'>#" . $exchangeOrder->getIncrementId() . '</a>';
                } catch (NoSuchEntityException $e) {
                    // exchange order was removed
                    $links[] = empty($rma->getExchangeOrderIncrements()[$id]) ? $id :
                        $rma->getExchangeOrderIncrements()[$id];
                }
            }
            $fieldset->addField('exchangeorder', 'note', [
                'label' => __('Exchange Order'),
                'text'  => implode(', ', $links),
            ]);
        }

    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return void
     */
    public function addCreditmemos($fieldset, \Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        if ($rma->getCreditMemoIds()) {
            $links = [];
            foreach ($rma->getCreditMemoIds() as $id) {
                $creditmemo = $this->creditMemoRepository->get($id);
                $links[] = "<a href='" . $this->getUrl(
                        'sales/creditmemo/view',
                        ['creditmemo_id' => $id]
                    ) . "'>#" . $creditmemo->getIncrementId() . '</a>';
            }
            $fieldset->addField('credit_memo_id', 'note', [
                'label' => __('Credit Memo'),
                'text'  => implode(', ', $links),
            ]);
        }
    }
}