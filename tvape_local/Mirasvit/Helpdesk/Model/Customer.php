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
 * @method Mirasvit_Helpdesk_Model_Resource_Customer_Collection|Mirasvit_Helpdesk_Model_Customer[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Customer load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Customer setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Customer setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Customer getResource()
 * @method int getCustomerId()
 * @method Mirasvit_Helpdesk_Model_Customer setCustomerId(int $id)
 * @method string getCustomerNote()
 * @method Mirasvit_Helpdesk_Model_Customer setCustomerNote(string $note)
 */
class Mirasvit_Helpdesk_Model_Customer extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('helpdesk/customer');
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function getNoteByCustomerId($customerId)
    {
        return Mage::getModel('helpdesk/customer')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem()
        ;
    }

    /************************/
}
