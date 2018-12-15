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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Additional extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var Mirasvit_Helpdesk_Model_Ticket $ticket */
        $ticket = Mage::registry('current_ticket');

        $fieldset = $form->addFieldset('edit_fieldset', array(
            'legend' => Mage::helper('helpdesk')->__('Additional Information')
        ));
        if ($ticket->getId()) {
            $fieldset->addField('ticket_id', 'hidden', array(
                'name' => 'ticket_id',
                'value' => $ticket->getId(),
            ));
        }
        if ($ticket->getId()) {
            $fieldset->addField('name', 'text', array(
                'label' => Mage::helper('helpdesk')->__('Subject'),
                'name' => 'name',
                'value' => $ticket->getName(),
            ));
        }
        $fieldset->addField('store_label', 'label', array(
            'name' => 'store_label',
            'label' => Mage::helper('helpdesk')->__('Store View'),
            'title' => Mage::helper('helpdesk')->__('Store View'),
            'value' => $ticket->getStore()->getName(),
        ));

        $fieldset->addField('store_id', 'hidden', array(
            'name' => 'store_id',
            'value' => $ticket->getStoreId(),
        ));

        $element = $fieldset->addField('channel', 'label', array(
            'label' => Mage::helper('helpdesk')->__('Channel'),
            'name' => 'channel',
            'value' => Mage::helper('helpdesk/channel')->getLabel($ticket->getChannel()),
        ));
        $data = $ticket->getChannelData();
        if (isset($data['url'])) {
            $element->setAfterElementHtml("&nbsp;<a id='view_customer_link' href='".$data['url']."' target='_blank'>" .
                Mage::helper('helpdesk')->__('open page') .
                '</a>');
        }
        if ($ticket->getId()) {
            $fieldset->addField('external_link', 'link', array(
                'label' => Mage::helper('helpdesk')->__('External Link'),
                'name' => 'external_link',
                'class' => 'external-link',
                'value' => $ticket->getExternalUrl(),
                'href' => $ticket->getExternalUrl(),
                'target' => '_blank',
            ));
        }
        $tags = array();
        foreach ($ticket->getTags() as $tag) {
            $tags[] = $tag->getName();
        }
        $fieldset->addField('tags', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Tags'),
            'name' => 'tags',
            'value' => implode(', ', $tags),
            'note' => Mage::helper('helpdesk')->__('comma-separated list'),
        ));

        if ($ticket->getCustomerId()) {
            /** @var Mirasvit_Helpdesk_Model_Customer $note */
            $note = Mage::getModel('helpdesk/customer')->getNoteByCustomerId($ticket->getCustomerId());
            $fieldset->addField('customer_note', 'textarea', array(
                'label' => Mage::helper('helpdesk')->__('Customer Notes'),
                'name' => 'customer_note',
                'value' => $note->getCustomerNote(),
                'note' => Mage::helper('helpdesk')->__('this info is the same for all tickets of this customer'),
            ));
        }

        return parent::_prepareForm();
    }

    /************************/

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $history = $this->getLayout()->createBlock('helpdesk/adminhtml_ticket_edit_tab_history')->toHtml();

        return parent::_toHtml().$history;
    }
}
