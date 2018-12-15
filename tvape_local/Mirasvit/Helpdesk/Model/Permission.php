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
 * @method Mirasvit_Helpdesk_Model_Resource_Permission_Collection|Mirasvit_Helpdesk_Model_Permission[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Permission load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Permission setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Permission setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Permission getResource()
 * @method int[] getDepartmentIds()
 * @method Mirasvit_Helpdesk_Model_Permission setDepartmentIds(array $ids)
 * @method bool getIsTicketRemoveAllowed()
 * @method Mirasvit_Helpdesk_Model_Permission setIsTicketRemoveAllowed(bool $flag)
 * @method bool getIsMessageEditAllowed()
 * @method Mirasvit_Helpdesk_Model_Permission setIsMessageEditAllowed(bool $flag)
 * @method bool getIsMessageRemoveAllowed()
 * @method Mirasvit_Helpdesk_Model_Permission setIsMessageRemoveAllowed(bool $flag)
 */
class Mirasvit_Helpdesk_Model_Permission extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/permission');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/

    public function loadDepartmentIds()
    {
        return $this->getResource()->loadDepartmentIds($this);
    }
}
