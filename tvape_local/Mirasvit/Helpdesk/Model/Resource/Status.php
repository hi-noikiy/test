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



class Mirasvit_Helpdesk_Model_Resource_Status extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/status', 'status_id');
    }

    protected function loadStoreIds(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Status $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/status_store'))
            ->where('ss_status_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['ss_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    protected function saveStoreIds($object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Status $object */
        $condition = $this->_getWriteAdapter()->quoteInto('ss_status_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/status_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = array(
                'ss_status_id' => $object->getId(),
                'ss_store_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('helpdesk/status_store'), $objArray);
        }
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Status $object */
        if (!$object->getIsMassDelete()) {
            $this->loadStoreIds($object);
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Status $object */
        if (!$object->getId()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Status $object */
        if (!$object->getIsMassStatus()) {
            $this->saveStoreIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
