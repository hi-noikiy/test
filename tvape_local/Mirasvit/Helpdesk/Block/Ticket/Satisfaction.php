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



class Mirasvit_Helpdesk_Block_Ticket_Satisfaction extends Mage_Core_Block_Template
{
    /**
     * @param int $rate
     *
     * @return string
     */
    public function getRateUrl($rate)
    {
        $message = $this->getMessage();

        return Mage::getUrl(
            'helpdesk/satisfaction/rate', array('rate' => $rate, 'message_owner' => $message->getUid())
        );
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $message = $this->getMessage();

        $collection = $this->getSatisfactions($message);
        if ($collection->count() || !Mage::getSingleton('helpdesk/config')->getSatisfactionIsShowInTicketFront()) {
            return '';
        } else {
            return parent::_toHtml();
        }
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
}
