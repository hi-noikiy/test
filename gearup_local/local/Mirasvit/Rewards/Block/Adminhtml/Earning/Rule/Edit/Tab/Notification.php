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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Block_Adminhtml_Earning_Rule_Edit_Tab_Notification extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var Mirasvit_Rewards_Model_Earning_Rule $earningRule */
        $earningRule = Mage::registry('current_earning_rule');

        $fieldset = $form->addFieldset('notification_fieldset',
            array('legend' => Mage::helper('rewards')->__('Notifications')));
        if ($earningRule->getId()) {
            $fieldset->addField('earning_rule_id', 'hidden', array(
                'name' => 'earning_rule_id',
                'value' => $earningRule->getId(),
            ));
        }
        $fieldset->addField('store_id', 'hidden', array(
            'name' => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ));

        $fieldset->addField('history_message', 'text', array(
            'label' => Mage::helper('rewards')->__('Message in the rewards history'),
            'required' => true,
            'name' => 'history_message',
            'value' => $earningRule->getHistoryMessage(),
            'note' => Mage::helper('rewards')->__('Customer will see this in his account'),
            'after_element_html' => ' [STORE VIEW]',
        ));

        $fieldset->addField('email_message', 'editor', array(
            'label' => Mage::helper('rewards')->__('Message for customer notification email'),
            'name' => 'email_message',
            'value' => $earningRule->getEmailMessage(),
            'config' => Mage::getSingleton('mstcore/wysiwyg_config')->getConfig(),
            'wysiwyg' => true,
            'note' => Mage::helper('rewards/message')->getNoteWithVariables(),
            'after_element_html' => ' [STORE VIEW]',
            'style' => 'width: 600px; height: 300px;',
        ));

        return parent::_prepareForm();
    }

    /************************/

    /**
     * @return string
     */
    public function getDefaultEmailMessage()
    {
        return
        '<p>Dear {{htmlescape var=$customer.name}},</p>
<p>Your account balance has been updated at <a href="{{store url=""}}">{{var store.getFrontendName()}}</a>. <p>
<ul>
<li>Balance Update: <b>{{var transaction_amount}}</b></li>
<li>Balance Total: <b>{{var balance_total}}</b></li>
</ul>
Thank you,<br>
<strong>{{var store.getFrontendName()}}</strong>
        ';
    }
}
