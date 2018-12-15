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



class Mirasvit_Helpdesk_Model_Resource_Ticket extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('helpdesk/ticket', 'ticket_id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Abstract|Mirasvit_Helpdesk_Model_Ticket
     */
    public function loadTagIds(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Ticket $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/ticket_tag'))
            ->where('tt_ticket_id = ?', $object->getId());
        $array = array();
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            foreach ($data as $row) {
                $array[] = $row['tt_tag_id'];
            }
        }
        $object->setData('tag_ids', $array);

        return $object;
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $object
     *
     * @return void
     */
    protected function saveTagIds($object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('tt_ticket_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/ticket_tag'), $condition);
        foreach ((array) $object->getData('tag_ids') as $id) {
            $objArray = array(
                'tt_ticket_id' => $object->getId(),
                'tt_tag_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('helpdesk/ticket_tag'), $objArray);
        }
    }

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $object
     *
     * @return void
     */
    protected function saveCustomerNote($object)
    {
        if ($object->getCustomerId() && $object->getCustomerNote()) {
            /** @var Mirasvit_Helpdesk_Model_Customer $customerNote */
            $customerNote = Mage::getModel('helpdesk/customer')->getNoteByCustomerId($object->getCustomerId());
            $customerNote->setCustomerId($object->getCustomerId())
                ->setCustomerNote($object->getCustomerNote())
                ->save()
            ;
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Ticket $object */
        if (!$object->getIsMassDelete()) {
            $this->loadTagIds($object);
        }
        if (is_string($object->getChannelData())) {
            $object->setChannelData(@unserialize($object->getChannelData()));
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->isNew = 0;

        /** @var  Mirasvit_Helpdesk_Model_Ticket $object */
        if (!$object->getId()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
            //give LastReplyAt a sensical date to default to (when the ticket was created)
            $object->setLastReplyAt(Mage::getSingleton('core/date')->gmtDate());
            $object->isNew = 1;
        }

        if (is_array($object->getChannelData())) {
            $object->setChannelData(serialize($object->getChannelData()));
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        $tags = array();
        foreach ($object->getTags() as $tag) {
            $tags[] = $tag->getName();
        }
        $object->setSearchIndex(implode(',', $tags));

        return parent::_beforeSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Ticket $object */
        if (!$object->getIsMassStatus()) {
            $this->saveTagIds($object);
            $this->saveCustomerNote($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
