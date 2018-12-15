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
 * @method Mirasvit_Helpdesk_Model_Satisfaction getFirstItem()
 * @method Mirasvit_Helpdesk_Model_Satisfaction getLastItem()
 * @method Mirasvit_Helpdesk_Model_Resource_Satisfaction_Collection|Mirasvit_Helpdesk_Model_Satisfaction[] addFieldToFilter
 * @method Mirasvit_Helpdesk_Model_Resource_Satisfaction_Collection|Mirasvit_Helpdesk_Model_Satisfaction[] setOrder
 */
class Mirasvit_Helpdesk_Model_Resource_Satisfaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/satisfaction');
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Helpdesk_Model_Satisfaction $item */
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
        /** @var Mirasvit_Helpdesk_Model_Satisfaction $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        // $select->joinLeft(array('message' => $this->getTable('helpdesk/message')), 'main_table.message_id = message.message_id', array('message_body' => 'message.body'));
        $select->joinLeft(array('ticket' => $this->getTable('helpdesk/ticket')), 'main_table.ticket_id = ticket.ticket_id', array('customer_name' => 'ticket.customer_name'));
        // $select->columns(array('is_replied' => new Zend_Db_Expr("answer <> ''")));
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}