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



class Mirasvit_Helpdesk_Block_Adminhtml_Ticket_Edit_Tab_Messages extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mst_helpdesk/ticket/edit/messages.phtml');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Message[]|Mirasvit_Helpdesk_Model_Resource_Message_Collection
     */
    public function getMessages()
    {
        return $this->getTicket()->getMessages(true);
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     * @returns string
     */
    public function getSourceUrl($message)
    {
        return $this->getUrl('*/*/source', array('message_id' => $message->getId()));
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     * @returns string
     */
    public function getDeleteUrl($message)
    {
        return $this->getUrl('*/*/deleteMessage', array(
            'message_id' => $message->getId(),
        ));
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     * @returns string
     */
    public function getMessageUrl($message)
    {
        return $this->getUrl('*/helpdesk_message/edit', array(
            'id' => $message->getId(),
        ));
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     *
     * @return Mirasvit_Helpdesk_Model_Resource_Satisfaction_Collection|Mirasvit_Helpdesk_Model_Satisfaction[]
     */
    public function getSatisfactions($message)
    {
        $collection = Mage::getModel('helpdesk/satisfaction')->getCollection()
            ->addFieldToFilter('message_id', $message->getId());

        return $collection;
    }

    /**
     * @return bool
     */
    public function isShowSatisfactions()
    {
        return Mage::getSingleton('helpdesk/config')->getSatisfactionIsShowResultsInTicket();
    }
}
