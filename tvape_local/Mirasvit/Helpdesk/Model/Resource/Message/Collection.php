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
 * @method Mirasvit_Helpdesk_Model_Message getFirstItem()
 * @method Mirasvit_Helpdesk_Model_Message getLastItem()
 * @method Mirasvit_Helpdesk_Model_Resource_Message_Collection|Mirasvit_Helpdesk_Model_Message[] addFieldToFilter
 * @method Mirasvit_Helpdesk_Model_Resource_Message_Collection|Mirasvit_Helpdesk_Model_Message[] setOrder
 */
class Mirasvit_Helpdesk_Model_Resource_Message_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/message');
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Helpdesk_Model_Message $item */
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $arr;
    }

    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('helpdesk')->__('-- Please Select --');
        }
        /** @var Mirasvit_Helpdesk_Model_Message $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    protected function joinFields()
    {
        $select = $this->getSelect();
        //$select->joinLeft(array('ticket' => $this->getTable('helpdesk/ticket')), 'main_table.ticket_id = ticket.ticket_id', array('ticket_name' => 'ticket.name'));
        $select->joinLeft(array('user' => $this->getTable('admin/user')), 'main_table.user_id = user.user_id', array('user_name' => 'CONCAT(firstname, " ", lastname)'));
        // $select->columns(array('is_replied' => new Zend_Db_Expr("answer <> ''")));
        $select->where('is_deleted = 0');
    }

    protected function _initSelect()
    {
        $this->joinFields();
        parent::_initSelect();
    }

     /************************/
}
