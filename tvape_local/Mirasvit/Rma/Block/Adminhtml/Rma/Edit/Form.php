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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



require 'app/code/local/Mirasvit/Rma/Block/Adminhtml/Rma/Edit/Renderer/Mfile.php';

class Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Constructor.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_rma/rma/edit/form.phtml');
        $this->_calculateExchangeAmounts();
    }

    /**
     * @return Mirasvit_Rma_Model_Rma
     */
    public function getRma()
    {
        return Mage::registry('current_rma');
    }


    /**
     * @return Varien_Data_Form
     */
    public function getGeneralInfoForm()
    {
        $form = new Varien_Data_Form();
        /** @var Mirasvit_Rma_Model_Rma $rma */
        $rma = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('edit_fieldset',
            array('legend' => Mage::helper('rma')->__('General Information')));
        if ($rma->getId()) {
            $fieldset->addField('rma_id', 'hidden', array(
                'name' => 'rma_id',
                'value' => $rma->getId(),
            ));
        }
        if ($rma->getTicketId()) {
            $fieldset->addField('ticket_id', 'hidden', array(
                'name' => 'ticket_id',
                'value' => $rma->getTicketId(),
            ));
        }

        $element = $fieldset->addField('increment_id', 'text', array(
            'label' => Mage::helper('rma')->__('RMA #'),
            'name' => 'increment_id',
            'value' => $rma->getIncrementId(),
        ));

        if (!$rma->getId()) {
            $element->setNote('will be generated automatically, if empty');
        }
        if (($customerId = $this->getRequest()->getParam('customer_id')) !== null && !$rma->getCustomerId()) {
            $rma->setCustomerId($customerId);
        }

        if ($rma->getOrders() && ($orderIds = $rma->getOrders()->getAllIds())) {
            $rmas = Mage::helper('rma')->getRmaByOrder($orderIds, $rma->getId());
        }

        if ($orderIds) {
            $orderLinks = array();
            foreach ($orderIds as $orderId) {
                $origOrder = Mage::getModel('sales/order')->load($orderId);
                if ($origOrder) {
                    $orderUrl = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view",
                        array('order_id' => $orderId));
                    $orderLinks[] = "<a href='".$orderUrl."' target='_blank'>".$origOrder->getIncrementId().'</a>';
                } else {
                    $orderLinks[] = $orderId;
                }
            }
            $fieldset->addField('order-note', 'note', array(
                'label' => $this->__('Original orders'),
                'text' => implode(', ', $orderLinks),
            ));
        }

        if ($rma->getCustomerId()) {
            $customerName = $rma->getName();
            if (trim($customerName) == '') {
                $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
                $customerName = $customer->getFirstname().' '.$customer->getLastname();
            }
            $fieldset->addField('customer', 'link', array(
                'label' => Mage::helper('rma')->__('Customer'),
                'name' => 'customer',
                'value' => $customerName,
                'href' => Mage::helper('rma/mage')->getBackendCustomerUrl($rma->getCustomerId()),
            ));
            $fieldset->addField('customer_id', 'hidden', array(
                'name' => 'customer_id',
                'value' => $rma->getCustomerId(),
            ));
        } else {
            $fieldset->addField('customer', 'label', array(
                'label' => Mage::helper('rma')->__('Customer'),
                'name' => 'customer',
                'value' => $rma->getName().($rma->getIsGift() ? ' '.Mage::helper('rma')->__('(received a gift)') : ''),
            ));
            $fieldset->addField('customer_id', 'hidden', array(
                'name' => 'customer_id',
                'value' => 0,
            ));
        }
        if ($this->getRequest()->getParam('orders_id') == -1) {
            $fieldset->addField('orders_id', 'hidden', array(
                'name' => 'orders_id',
                'value' => -1,
            ));
        }
        if ($ticket = $rma->getTicket()) {
            $fieldset->addField('ticket', 'link', array(
                'label' => Mage::helper('rma')->__('Created From Ticket'),
                'name' => 'ticket',
                'value' => '#'.$ticket->getCode(),
                'href' => $ticket->getBackendUrl(),
            ));
        }
        $fieldset->addField('user_id', 'select', array(
            'label' => Mage::helper('rma')->__('RMA Owner'),
            'name' => 'user_id',
            'value' => $rma->getUserId(),
            'values' => Mage::helper('rma')->toAdminUserOptionArray(true),
        ));

        $fieldset->addField('status_id', 'select', array(
            'label' => Mage::helper('rma')->__('Status'),
            'name' => 'status_id',
            'value' => $rma->getStatusId(),
            'values' => Mage::getModel('rma/status')->getCollection()->setOrder('sort_order', 'asc')->toOptionArray(),
        ));

        $fieldset->addField('return_address_id', 'select', array(
            'label' => Mage::helper('rma')->__('Alternative Return Address'),
            'name' => 'return_address_id',
            'value' => $rma->getReturnAddressId(),
            'values' => Mage::getModel('rma/return_address')->getCollection()->setOrder('sort_order', 'asc')
                ->toOptionArray(true),
        ));

        $fieldset->addField('return_label', 'mfile', array(
            'label' => Mage::helper('rma')->__('Upload Return Label'),
            'name' => 'return_label',
            'attachment' => $rma->getReturnLabel(),
        ));

        // Add other RMA on this order
        $rmaLinks = array();
        if (!empty($rmas) && $rmas->count()) {
            foreach ($rmas as $currentRma) {
                $rmaLinks[] = "<a href='".$currentRma->getBackendUrl()."'>".$currentRma->getIncrementId().'</a>';
            }
            $fieldset->addField('rma-note', 'note', array(
                'label' => $this->__('Another RMA for this orders'),
                'text' => implode(', ', $rmaLinks),
            ));
        }

        if ($rma->getId()) {
            $fieldset->addField('guest_link', 'link', array(
                'label' => Mage::helper('rma')->__('External Link'),
                'name' => 'guest_link',
                'class' => 'guest-link',
                'value' => Mage::helper('rma')->__('open'),
                'href' => $rma->getGuestUrl(),
                'target' => '_blank',
            ));
        }

        if ($rma->getExchangeOrderIds()) {
            $links = array();
            foreach ($rma->getExchangeOrderIds() as $id) {
                $exchageOrder = Mage::getModel('sales/order')->load($id);
                $links[] = "<a href='".$this->getUrl('adminhtml/sales_order/view',
                        array('order_id' => $id))."'>#".$exchageOrder->getIncrementId().'</a>';
            }
            $fieldset->addField('exchangeorder', 'note', array(
                'label' => $this->__('Exchange Order'),
                'text' => implode(', ', $links),
            ));
        }

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getFieldForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');
        $fieldset = $form->addFieldset('field_fieldset',
            array('legend' => Mage::helper('rma')->__('Additional Information')));
        $collection = Mage::helper('rma/field')->getStaffCollection();
        if (!$collection->count()) {
            return false;
        }
        foreach ($collection as $field) {
            $fieldset->addField($field->getCode(),
                $field->getType(), Mage::helper('rma/field')->getInputParams($field, true, $rma));
        }

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getCustomerForm()
    {
        $data = Mage::getSingleton('adminhtml/session')->getCustomerData(true);
        $form = new Varien_Data_Form();
        if (
            Mage::getSingleton('admin/session')->isAllowed('customer/manage') &&
            ($customerId = $this->getRequest()->getParam('customer_id')) !== null &&
            !$customerId
        ) {
            $form->setFieldNameSuffix('new_customer');
            $fieldset = $form->addFieldset(
                'field_fieldset',
                array('legend' => Mage::helper('rma')->__('Register New Customer'))
            );

            $customer = Mage::getModel('customer/customer')->load(0);

            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setEntity($customer)
                ->setFormCode('adminhtml_customer')
                ->initDefaultValues();

            $attributes = $customerForm->getAttributes();
            foreach ($attributes as $key => $attribute) {
                if (!$attribute['is_required']) {
                    unset($attributes[$key]);
                }
            }

            $this->_setFieldset($attributes, $fieldset);
            foreach ($fieldset->getElements() as $element) {
                if (isset($data['new_customer'][$element->getId()])) {
                    $element->setValue($data['new_customer'][$element->getId()]);
                }
            }
        }

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getTemplateForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');
        $collection = Mage::getModel('rma/template')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setOrder('name', 'asc');
        if ($rma->getId()) {
            $collection->addStoreFilter($rma->getStoreId());
        }

        if ($collection->count()) {
            if ($rma->getId()) {
                $options = $collection->toOptionArray(true);
            } else {
                $options = $collection->toOptionGroupArray(true);
            }
            $element = $form->addField('template_id', 'select', array(
                'label' => Mage::helper('rma')->__('Insert Quick Response'),
                'name' => 'template_id',
                'value' => $rma->getTemplateId(),
                'values' => $options,
                'onchange' => 'checkWYSIWYG(this)',
            ));

            $values = array();
            foreach ($collection as $template) {
                $text = trim($template->getParsedTemplate($rma));
                $values[] = "<div id='htmltemplate-{$template->getId()}' style='display:none;'>{$text}</div>";
            }
            $element->setAfterElementHtml(implode("\n", $values));
        }

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getEditorForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');
        if (Mage::getSingleton('rma/config')->getGeneralIsWysiwygEnabled($rma->getStore()->getId())) {
            $form->addField('reply', 'editor', array(
                'required' => false,
                'name' => 'reply',
                'value' => '',
                'config' => Mage::getSingleton('rma/config_wysiwyg')->getConfig(),
                'wysiwyg' => true,
                'style' => 'height:15em',
            ));
        } else {
            $form->addField('reply', 'textarea', array(
                'required' => false,
                'name' => 'reply',
                'value' => '',
                'rows' => 2,
                'cols' => 60,
                'style' => 'width: 92%',
            ));
        }
        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getShippingAddressForm()
    {
        $form = new Varien_Data_Form();
        if (!$this->getCustomerForm()->getHtml()) {
            $rma = Mage::registry('current_rma');

            $fieldset = $form->addFieldset('customer_fieldset',
                array('legend' => Mage::helper('rma')->__('Contact Information')));
            $fieldset->addField('firstname', 'text', array(
                'label' => Mage::helper('rma')->__('First Name'),
                'name' => 'firstname',
                'value' => $rma->getFirstname(),
            ));
            $fieldset->addField('lastname', 'text', array(
                'label' => Mage::helper('rma')->__('Last Name'),
                'name' => 'lastname',
                'value' => $rma->getLastname(),
            ));
            $fieldset->addField('company', 'text', array(
                'label' => Mage::helper('rma')->__('Company'),
                'name' => 'company',
                'value' => $rma->getCompany(),
            ));
            $fieldset->addField('telephone', 'text', array(
                'label' => Mage::helper('rma')->__('Telephone'),
                'name' => 'telephone',
                'value' => $rma->getTelephone(),
            ));
            $fieldset->addField('email', 'text', array(
                'label' => Mage::helper('rma')->__('Email'),
                'name' => 'email',
                'value' => $rma->getEmail(),
            ));
            $street = explode("\n", $rma->getStreet());
            $fieldset->addField('street', 'hidden', array(
                'label' => Mage::helper('rma')->__('Street Address'),
                'name' => 'street',
                'value' => $street[0],
            ));
            $fieldset->addField('street2', 'hidden', array(
                // 'label'     => Mage::helper('rma')->__('Street Address'),
                'name' => 'street2',
                'value' => isset($street[1]) ? $street[1] : '',
            ));
            $fieldset->addField('city', 'hidden', array(
                'label' => Mage::helper('rma')->__('City'),
                'name' => 'city',
                'value' => $rma->getCity(),
            ));
            $fieldset->addField('postcode', 'hidden', array(
                'label' => Mage::helper('rma')->__('Zip/Postcode'),
                'name' => 'postcode',
                'value' => $rma->getPostcode(),
            ));
        }

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getExchangeOrderForm()
    {
        $form = new Varien_Data_Form();
        $rma = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('customer_fieldset',
            array('legend' => Mage::helper('rma')->__('Exchange Order Information')));
        $fieldset->addField('payment_method', 'select', array(
            'label' => Mage::helper('rma')->__('Payment Method'),
            'name' => 'payment_method',
        ));
        $fieldset->addField('shipping_method', 'select', array(
            'label' => Mage::helper('rma')->__('Shipping Method'),
            'name' => 'shipping_method',
        ));
        $fieldset->addField('shipping_cost', 'text', array(
            'label' => Mage::helper('rma')->__('Shipping Cost'),
            'name' => 'shipping_cost',
            'value' => 0,
        ));

        return $form;
    }

    /**
     * @return Varien_Data_Form
     */
    public function getOfflineOrderNameForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form;

        $fieldset->addField('offline_order_name', 'text', array(
            'label' => Mage::helper('rma')->__('Order or Receipt #'),
            'name' => 'offline[order_name][]',
            'value' => '',
            'class' => 'UI-OFFLINE-ORDER-INPUT',
        ));
        $fieldset->addField('order_id_input', 'hidden', array(
            'name' => 'order_id[]',
            'class' => 'UI-ORDER-ID-INPUT',
        ));

        return $form;
    }

    /**
     * @return string
     */
    public function getOrdersHtml()
    {
        $block = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_orderList', 'rma_order');

        return $block->toHtml();
    }


    /**
     * @return string
     */
    public function getOfflineOrdersHtml()
    {
        $block = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_offline_orderList', 'rma_offline_order');

        return $block->toHtml();
    }


    /**
     * @return string
     */
    public function getHistoryHtml()
    {
        $historyBlock = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_history', 'rma_history');

        return $historyBlock->toHtml();
    }

    /**
     * @return string
     */
    public function getFedExHtml()
    {
        $config = Mage::getSingleton('rma/config');
        if ($this->getRma()->getId() && $config->getFedexFedexEnable($this->getRma()->getStoreId())) {
            $fedexBlock = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_fedex', 'fedex_block');

            return $fedexBlock->toHtml();
        }
    }


    /**
     * @return string
     */
    public function getFedExLabelHtml()
    {
        $config = Mage::getSingleton('rma/config');
        if ($this->getRma()->getId() && $config->getFedexFedexEnable($this->getRma()->getStoreId())) {
            $fedexBlock = $this->getLayout()->createBlock('rma/adminhtml_rma_edit_form_fedexLabel',
                'fedex_block_labels');

            return $fedexBlock->toHtml();
        }
    }



    /**
     * @return string
     */
    public function getReturnAddressHtml()
    {
        $address = $this->getRma()->getReturnAddressHtml();

        return $address;
    }

    /**
     * @return Mirasvit_Rma_Model_Reason[]|Mirasvit_Rma_Model_Resource_Reason_Collection
     */
    public function getReasonCollection()
    {
        return Mage::getModel('rma/reason')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    /**
     * @return Mirasvit_Rma_Model_Resolution[]|Mirasvit_Rma_Model_Resource_Resolution_Collection
     */
    public function getResolutionCollection()
    {
        return Mage::getModel('rma/resolution')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }

    /**
     * @return Mirasvit_Rma_Model_Condition[]|Mirasvit_Rma_Model_Resource_Condition_Collection
     */
    public function getConditionCollection()
    {
        return Mage::getModel('rma/condition')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', 'asc');
    }
    /************************/

    /**
     * @param bool $isRead
     *
     * @return string
     */
    public function getMarkUrl($isRead)
    {
        return $this->getUrl('*/*/markRead', array('rma_id' => $this->getRma()->getId(), 'is_read' => (int) $isRead));
    }

    /**
     * @return bool
     */
    public function getIsCreditEnabled()
    {
        return Mage::helper('mstcore')->isModuleEnabled('Mirasvit_Credit');
    }

    /**
     * @return float
     */
    public function getCreditAmount()
    {
        $balance = Mage::getModel('credit/balance')->loadByCustomer($this->getRma()->getCustomer());

        return $balance->getAmount();
    }

    /**
     * Old exchange amount.
     */
    protected $oldAmount;

    /**
     * New exchange amount.
     */
    protected $newAmount;

    /**
     */
    protected function _calculateExchangeAmounts()
    {
        $rma = $this->getRma();
        $this->oldAmount = 0;
        $this->newAmount = 0;
        foreach (Mage::helper('rma')->getRmaItemsByRma($rma) as $item) {
            if ($item->isExchange() || $item->isCredit()) {
                $this->oldAmount += $item->getOrderItem()->getPriceInclTax() * $item->getQtyRequested();
            }
            if ($item->isExchange()) {
                $this->newAmount += $item->getExchangeProduct()->getFinalPrice() * $item->getQtyRequested();
            }
        }
    }

    /**
     * @return float
     */
    public function getExchangeOldAmount()
    {
        return $this->oldAmount;
    }

    /**
     * @return float
     */
    public function getExchangeNewAmount()
    {
        return $this->newAmount;
    }

    /**
     * @return float
     */
    public function getExchangeDiffAmount()
    {
        return $this->newAmount - $this->oldAmount;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getCreditmemoUrl($rma, $order)
    {
        $collection = Mage::getModel('sales/order_invoice')->getCollection()
            ->addFieldToFilter('order_id', $order->getId());

        if ($collection->count() == 1) {
            $invoice = $collection->getFirstItem();

            return $this->getUrl('adminhtml/sales_order_creditmemo/new',
                array('order_id' => $order->getId(), 'invoice_id' => $invoice->getId(), 'rma_id' => $rma->getId()));
        } else {
            return $this->getUrl('adminhtml/sales_order_creditmemo/new',
                array('order_id' => $order->getId(), 'rma_id' => $rma->getId()));
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getStoreCreditRefundUrl($rma, $order)
    {
        return $this->getUrl('*/rma_rma/refundToCredit',
            array('rma_id' => $rma->getId(), 'order_id' => $order->getId()));
    }
}
