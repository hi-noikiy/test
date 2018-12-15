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



class Mirasvit_Helpdesk_Block_Satisfaction_Form extends Mage_Core_Block_Template
{
    /**
     * @return string
     */
    public function getPostUrl()
    {
        return Mage::getUrl(
            'helpdesk/satisfaction/post',
            array(
                'message_owner' => $this->getUid(),
                'satisfaction' => $this->getSatisfactionId()
            )
        );
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return Mage::app()->getRequest()->getParam('message_owner');
    }

    /**
     * @return int
     */
    public function getSatisfactionId()
    {
        return Mage::app()->getRequest()->getParam('satisfaction');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getSatisfaction()
    {
        return Mage::getModel('helpdesk/satisfaction')->load($this->getSatisfactionId());
    }

    /**
     * @return Mirasvit_Helpdesk_Model_Ticket
     */
    public function getTicket()
    {
        return $this->getSatisfaction()->getTicket();
    }
}
