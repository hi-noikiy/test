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



class Mirasvit_Helpdesk_Model_Resource_ThirdPartyEmail extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/third_party_email', 'third_party_email_id');
    }

    protected function loadStoreIds(Mage_Core_Model_Abstract $object)
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/third_party_email_store'))
            ->where('ees_third_party_email_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['ees_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    protected function loadDepartmentIds(Mage_Core_Model_Abstract $object)
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('helpdesk/third_party_email_department'))
            ->where('eed_third_party_email_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['eed_department_id'];
            }
            $object->setData('department_ids', $array);
        }

        return $object;
    }

    protected function saveStoreIds($object)
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        $condition = $this->_getWriteAdapter()->quoteInto('ees_third_party_email_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/third_party_email_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = array(
                'ees_third_party_email_id' => $object->getId(),
                'ees_store_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('helpdesk/third_party_email_store'), $objArray);
        }
    }

    protected function saveDepartmentIds($object)
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        $condition = $this->_getWriteAdapter()->quoteInto('eed_third_party_email_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('helpdesk/third_party_email_department'), $condition);
        foreach ((array) $object->getData('department_ids') as $id) {
            $objArray = array(
                'eed_third_party_email_id' => $object->getId(),
                'eed_department_id' => $id,
            );
            $this->_getWriteAdapter()->insert($this->getTable('helpdesk/third_party_email_department'), $objArray);
        }
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        if (!$object->getIsMassDelete()) {
            $this->loadStoreIds($object);
            $this->loadDepartmentIds($object);
        }

        return parent::_afterLoad($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_ThirdPartyEmail $object */
        if (!$object->getIsMassStatus()) {
            $this->saveStoreIds($object);
            $this->saveDepartmentIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
