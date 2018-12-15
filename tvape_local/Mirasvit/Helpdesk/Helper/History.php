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



class Mirasvit_Helpdesk_Helper_History extends Mage_Core_Helper_Abstract
{
    public static $history = array();

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param string                         $triggeredBy
     * @param $by
     *
     * @return mixed
     */
    public static function getHistoryRecord($ticket, $triggeredBy, $by)
    {
        if (!isset(self::$history[$ticket->getId()])) {
            $history = Mage::getModel('helpdesk/history');
            $history->setTicketId($ticket->getId());
            $history->setTriggeredBy($triggeredBy);
            self::$history[$ticket->getId()] = $history;
        }

        switch ($triggeredBy) {
            case Mirasvit_Helpdesk_Model_Config::CUSTOMER:
                self::$history[$ticket->getId()]->addName($by['customer']->getName());
                break;
            case Mirasvit_Helpdesk_Model_Config::USER:
                self::$history[$ticket->getId()]->addName($by['user']->getName());
                break;
            case Mirasvit_Helpdesk_Model_Config::THIRD:
                self::$history[$ticket->getId()]->addName($by['email']->getSenderNameOrEmail());
                break;
            case Mirasvit_Helpdesk_Model_Config::RULE:
                self::$history[$ticket->getId()]->addName($by['rule']->getName());
                break;
        }

        return self::$history[$ticket->getId()];
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     * @param string                         $triggeredBy
     * @param array                          $by
     */
    public function changeTicket($ticket, $triggeredBy, $by)
    {
        $history = self::getHistoryRecord($ticket, $triggeredBy, $by);
        $text = array();
        if ($ticket->getStatusId() != $ticket->getOrigData('status_id')) {
            if ($ticket->getOrigData('status_id')) {
                $oldStatus = Mage::getModel('helpdesk/status')->load($ticket->getOrigData('status_id'));
                $text[] = $this->__('Ticket status changed from: %s to: %s', $oldStatus->getName(), $ticket->getStatus()->getName());
            } else {
                $text[] = $this->__('Ticket status set to: %s', $ticket->getStatus()->getName());
            }
            $history->setToStatusId($ticket->getStatusId())
                ->setFromStatusId($ticket->getOrigData('status_id'))
                ->save()
            ;
        }
        if ($ticket->getPriorityId() != $ticket->getOrigData('priority_id')) {
            if ($ticket->getOrigData('priority_id')) {
                $oldPriority = Mage::getModel('helpdesk/priority')->load($ticket->getOrigData('priority_id'));
                $text[] = $this->__('Ticket priority changed from: %s to: %s', $oldPriority->getName(), $ticket->getPriority()->getName());
            } else {
                $text[] = $this->__('Ticket priority set to: %s', $ticket->getPriority()->getName());
            }
        }
        if ($ticket->getUserId() != $ticket->getOrigData('user_id')) {
            if ($ticket->getOrigData('user_id')) {
                $oldUser = Mage::getModel('admin/user')->load($ticket->getOrigData('user_id'));
                if ($oldUser && $ticket->getUser()) {
                    $text[] = $this->__('Ticket owner changed from: %s to: %s', $oldUser->getName(), $ticket->getUser()->getName());
                }
            } else {
                $text[] = $this->__('Ticket owner set to: %s', $ticket->getUser()->getName());
            }
            $history->setToUserId($ticket->getUserId())
                ->setFromUserId($ticket->getOrigData('user_id'))
                ->save()
            ;
        }
        if ($ticket->getDepartmentId() != $ticket->getOrigData('department_id')) {
            if ($ticket->getOrigData('department_id')) {
                $oldDepartment = Mage::getModel('helpdesk/department')->load($ticket->getOrigData('department_id'));
                $text[] = $this->__('Ticket department changed from: %s to: %s', $oldDepartment->getName(), $ticket->getDepartment()->getName());
            } else {
                $text[] = $this->__('Ticket department set to: %s', $ticket->getDepartment()->getName());
            }
        }
        if ($ticket->getIsArchived() != $ticket->getOrigData('is_archived')) {
            if ($ticket->getIsArchived()) {
                $text[] = $this->__('Ticket was moved to archive');
            } else {
                $text[] = $this->__('Ticket was moved from archive');
            }
        }
        if ($ticket->getMergedTicketId()) {
            $newTicket = Mage::getModel('helpdesk/ticket')->load($ticket->getMergedTicketId());
            $text[] = $this->__('Ticket was merged to: %s', $newTicket->getCode());
        }
        if (isset($by['codes'])) {
            $text[] = $this->__('Ticket was merged with: %s', implode(', ', $by['codes']));
        }
        if ($oldData = $ticket->getOrigData()) {
            $map = $this->getFieldMap();
            foreach ($oldData as $k => $v) {
                if (in_array($k, array(
                    'user_id',
                    'department_id',
                    'fp_period_value',
                    'fp_execute_at',
                    'fp_execute_at',
                    'fp_department_id',
                    'fp_department_id',
                    'fp_user_id',
                    'updated_at',
                    'last_reply_at',
                    'reply_cnt',
                    'is_archived',
                    'status_id',
                    'priority_id',
                ))) {
                    continue;
                }
                if ($ticket->getData($k) != $v) {
                    $label = $k;
                    if (isset($map[$k])) {
                        $label = $map[$k];
                    } elseif ($field = Mage::helper('helpdesk/field')->getFieldByCode($k)) {
                        $data = Mage::helper('helpdesk/field')->getInputParams($field, false, $ticket);
                        if (!empty($data['label'])) {
                            $label = $data['label'];
                        }
                    }
                    if ($v === null || $v === '') {
                        $v = '""';
                    }
                    $text[] = $this->__('%s changed from: %s to: %s', $label, $v, $ticket->getData($k));
                }
            }
        }
        $history->addMessage($text);
    }

    public function addMessage($ticket, $text, $triggeredBy, $by, $messageType)
    {
        $history = self::getHistoryRecord($ticket, $triggeredBy, $by);
        $text = array();
        switch ($messageType) {
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC:
                $text[] = $this->__('Message added to ticket');
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL:
                $text[] = $this->__('Internal note added to ticket');
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC_THIRD:
                $text[] = $this->__('Third party message added to ticket');
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL_THIRD:
                $text[] = $this->__('Private third party message added to ticket');
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_REMOVED:
                $text[] = $this->__('Message was removed');
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_RESTORED:
                $text[] = $this->__('Message was restored');
                break;
        }
        $history->addMessage($text);
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Message $message
     * @param string                          $triggeredBy
     * @param array                           $by
     * @param string                          $messageType
     */
    public function changeMessage($message, $triggeredBy, $by, $messageType)
    {
        $ticket = MAge::getModel('helpdesk/ticket')->load($message->getTicketId());
        $history = self::getHistoryRecord($ticket, $triggeredBy, $by);
        $text = array();
        switch ($messageType) {
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_REMOVED:
                $text[] = $this->__('Message was deleted (%s)', $message->getId());
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_RESTORED:
                $text[] = $this->__('Message was restored (%s)', $message->getId());
                break;
            case Mirasvit_Helpdesk_Model_Config::MESSAGE_EDIT:
                $text[] = $this->__('Message was changed (%s)', $message->getId());
                break;
        }
        $history->addMessage($text);
    }

    /**
     * @return array
     */
    protected function getFieldMap()
    {
        return array(
            'ticket_id' => $this->__('Ticket ID'),
            'code' => $this->__('Code'),
            'external_id' => $this->__('External ID'),
            'user_id' => $this->__('User ID'),
            'name' => $this->__('Name'),
            'description' => $this->__('Description'),
            'priority_id' => $this->__('Priority ID'),
            'status_id' => $this->__('Status ID'),
            'department_id' => $this->__('Department ID'),
            'customer_id' => $this->__('Customer ID'),
            'quote_address_id' => $this->__('Quote Address ID'),
            'customer_email' => $this->__('Customer Email'),
            'customer_name' => $this->__('Customer Name'),
            'order_id' => $this->__('Order ID'),
            'last_reply_name' => $this->__('Last reply name'),
            'last_reply_at' => $this->__('Last reply at'),
            'reply_cnt' => $this->__('Reply count'),
            'store_id' => $this->__('Store ID'),
            'created_at' => $this->__('Created At'),
            'updated_at' => $this->__('Updated At'),
            'is_spam' => $this->__('Is Spam'),
            'email_id' => $this->__('Email ID'),
            'first_reply_at' => $this->__('First reply at'),
            'first_solved_at' => $this->__('First solved at'),
            'is_archived' => $this->__('Is archived'),
            'fp_period_unit' => $this->__('FP period unit'),
            'fp_period_value' => $this->__('FP period value'),
            'fp_execute_at' => $this->__('FP executed at'),
            'fp_is_remind' => $this->__('FP is remind'),
            'fp_remind_email' => $this->__('FP remind email'),
            'fp_priority_id' => $this->__('FP priority ID'),
            'fp_status_id' => $this->__('FP status ID'),
            'fp_department_id' => $this->__('FP department ID'),
            'fp_user_id' => $this->__('FP user ID'),
            'channel' => $this->__('Channel'),
            'channel_data' => $this->__('Channel Data'),
            'third_party_email' => $this->__('Third party email'),
            'search_index' => $this->__('Search index'),
            'cc	text' => $this->__('CC text'),
            'bcc' => $this->__('BCC'),
            'merged_ticket_id' => $this->__('Merged ticket ID'),
        );
    }
}
