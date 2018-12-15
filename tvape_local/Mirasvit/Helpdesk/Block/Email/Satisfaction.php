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



/**
 * #### ADDED BY MIRASVIT ####.
 *
 * @method Mirasvit_Helpdesk_Model_Ticket getTicket()
 * @method $this setTicket(Mirasvit_Helpdesk_Model_Ticket $param)

 * #### END ADDED BY MIRASVIT ####
 */
class Mirasvit_Helpdesk_Block_Email_Satisfaction extends Mage_Core_Block_Template
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'frontend');
    }

    /**
     * @param int $rate
     * @return string
     */
    public function getRateUrl($rate)
    {
        $message = $this->getTicket()->getLastMessage();

        return Mage::getUrl(
            'helpdesk/satisfaction/rate', array('rate' => $rate, 'message_owner' => $message->getUid())
        );
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!Mage::getSingleton('helpdesk/config')->getSatisfactionIsActive() || !$this->getTicket()) {
            return '';
        }

        return parent::_toHtml();
    }
}
