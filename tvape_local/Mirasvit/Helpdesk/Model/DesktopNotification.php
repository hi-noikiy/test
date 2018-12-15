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



class Mirasvit_Helpdesk_Model_DesktopNotification extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('helpdesk/desktopNotification');
    }

    /**
     * @var Mirasvit_Helpdesk_Model_Ticket
     */
    protected $_ticket = null;

    /**
     * @return bool|Mirasvit_Helpdesk_Model_Ticket
     */
    public function getTicket()
    {
        if (!$this->getTicketId()) {
            return false;
        }
        if ($this->_ticket === null) {
            $this->_ticket = Mage::getModel('helpdesk/ticket')->load($this->getTicketId());
        }

        return $this->_ticket;
    }

    /**
     * @return array
     */
    public function getReadByUserIds() {
        return explode(',', $this->getData('read_by_user_ids'));
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setReadByUserIds($ids) {
        $this->setData('read_by_user_ids', ','.implode(',', array_unique(array_filter($ids))).',');
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function addReadByUserId($id) {
        $ids = $this->getReadByUserIds();
        $ids[] = $id;
        $this->setReadByUserIds($ids);
        return $this;
    }
}