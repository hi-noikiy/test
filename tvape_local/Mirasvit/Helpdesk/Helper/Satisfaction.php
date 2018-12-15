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



class Mirasvit_Helpdesk_Helper_Satisfaction extends Varien_Object
{


    public function addRate($messageUid, $rate)
    {
        // Ban adding rates from Google IPs
        $bannedIps = array(
            '/66\.102\.([0-9]|1[0-5])\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))/',
            '/70\.39\.157\.(1(9[2-9])|2([0-1][0-9]|2[0-3]))/',
            '/72\.30\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))/'
        );

        $bannedAgents = array(
            'yahoo', 'yandex', 'google',
            'baidu', 'preview', 'image',
        );

        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        foreach($bannedIps as $pattern) {
            $matches = null;
            if(preg_match($pattern, $remoteAddr, $matches)) {
                return false;
            }
        }

        $remoteAgent = Mage::helper('core/http')->getHttpUserAgent();
        foreach($bannedAgents as $agent) {
            if(stripos($remoteAgent, $agent)) {
                return false;
            }
        }


        $message = $this->getMessageByUid($messageUid);
        $satisfaction = $this->getSatisfactionByMessage($message);

        $ticket = Mage::getModel('helpdesk/ticket')->load($message->getTicketId());

        $satisfaction->setTicketId($message->getTicketId())
            ->setMessageId($message->getId())
            ->setCustomerId($message->getCustomerId())
            ->setUserId($message->getUserId())
            ->setStoreId($ticket->getStoreId())
            ->setRate($rate)
            ->save();

        Mage::helper('helpdesk/mail')->sendNotificationStaffNewSatisfaction($satisfaction);

        return $satisfaction;
    }

    public function addComment($messageUid, $comment)
    {
        $message = $this->getMessageByUid($messageUid);
        $satisfaction = $this->getSatisfactionByMessage($message);
        $satisfaction->setComment($comment)
            ->save();

        Mage::helper('helpdesk/mail')->sendNotificationStaffNewSatisfaction($satisfaction);
    }

    public function getMessageByUid($messageUid)
    {
        $messages = Mage::getModel('helpdesk/message')->getCollection()
                    ->addFieldToFilter('uid', $messageUid);
        if (!$messages->count()) {
            throw new Mage_Core_Exception('Wrong URL');
        }
        $message = $messages->getFirstItem();

        return $message;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     *
     * @return Mirasvit_Helpdesk_Model_Satisfaction
     */
    public function getSatisfactionByMessage($message)
    {
        $satisfactions = Mage::getModel('helpdesk/satisfaction')->getCollection()
            ->addFieldToFilter('message_id', $message->getId());
        if ($satisfactions->count()) {
            $satisfaction = $satisfactions->getFirstItem();
        } else {
            $satisfaction = Mage::getModel('helpdesk/satisfaction');
        }

        return $satisfaction;
    }
}
