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



class Mirasvit_Helpdesk_Model_Resource_Draft extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/draft', 'draft_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Draft $object */
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Helpdesk_Model_Draft $object */
        // if (!$object->getId()) {
        //     $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        // }
        // $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        $value = $object->getData('users_online');
        if (is_array($value)) {
            $object->setData('users_online', serialize($value));
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Helpdesk_Model_Draft $object */
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /************************/
}
