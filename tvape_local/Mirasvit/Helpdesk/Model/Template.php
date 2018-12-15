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
 * @method Mirasvit_Helpdesk_Model_Resource_Template_Collection|Mirasvit_Helpdesk_Model_Template[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Template load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Template setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Template setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Template getResource()
 * @method string getTemplate()
 * @method $this setTemplate(string $param)
 * @method string getName()
 * @method $this setName(string $param)
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $param)
 * @method int[] getStoreIds()
 * @method $this setStoreIds(array $param)
 */
class Mirasvit_Helpdesk_Model_Template extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/template');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/

    /**
     * @param Mirasvit_Helpdesk_Model_Ticket $ticket
     *
     * @return string
     */
    public function getParsedTemplate($ticket)
    {
        $storeId = $ticket->getStoreId();
        $storeOb = Mage::getModel('core/store')->load($storeId);
        if (!$name = Mage::getStoreConfig('general/store_information/name', $storeId)) {
            $name = $storeOb->getName();
        }
        $store = new Varien_Object(array(
            'name' => $name,
            'phone' => Mage::getStoreConfig('general/store_information/phone', $storeId),
            'address' => Mage::getStoreConfig('general/store_information/address', $storeId),
        ));
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        $result = Mage::helper('mstcore/parsevariables')->parse($this->getTemplate(), array(
            'ticket' => $ticket,
            'store' => $store,
            'user' => $user,
            'order' => $ticket->getOrderId() ? $ticket->getOrder() : new Varien_Object(),
            ),
            array(), $store->getId());

        return $result;
    }
}
