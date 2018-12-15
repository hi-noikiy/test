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
 * @method Mirasvit_Helpdesk_Model_Resource_Draft_Collection|Mirasvit_Helpdesk_Model_Draft[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Draft load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Draft setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Draft setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Draft getResource()
 * @method int getTicketId()
 * @method Mirasvit_Helpdesk_Model_Draft setTicketId(int $param)
 * @method Mirasvit_Helpdesk_Model_Draft setUsersOnline(int $param)
 * @method int getUpdatedBy()
 * @method Mirasvit_Helpdesk_Model_Draft setUpdatedBy(int $param)
 * @method string getBody()
 * @method Mirasvit_Helpdesk_Model_Draft setBody(string $param)
 * @method string getUpdatedAt()
 * @method Mirasvit_Helpdesk_Model_Draft setUpdatedAt(string $param)
 */
class Mirasvit_Helpdesk_Model_Draft extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/draft');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/

    public function getUsersOnline()
    {
        $value = $this->getData('users_online');
        if (is_array($value)) {
            return $value;
        }
        $value = unserialize($value);
        $this->setData('users_online', $value);

        return $value;
    }

    protected $_user = null;
    public function getUser()
    {
        if (!$this->getUpdatedBy()) {
            return false;
        }
        if ($this->_user === null) {
            $this->_user = Mage::getModel('admin/user')->load($this->getUpdatedBy());
        }

        return $this->_user;
    }

    /************************/
}
