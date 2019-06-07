<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Checkoutspage_Model_System_Config_CustomerGroups  extends Mage_Adminhtml_Block_System_Config_Form_Field 
  implements Varien_Data_Form_Element_Renderer_Interface
{

    public function toOptionArray()
    {
        $groups = Mage::getModel('customer/group')->getCollection();
        $values = array();

        foreach ($groups as $group) {
            $values[] = array('value' => $group->getCustomerGroupId(), 'label'=> $group->getCustomerGroupCode());
        }

        return $values;
    }


}