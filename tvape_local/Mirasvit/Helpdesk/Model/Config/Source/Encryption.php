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



class Mirasvit_Helpdesk_Model_Config_Source_Encryption
{
    public function toArray()
    {
        return array(
            Mirasvit_Helpdesk_Model_Config::ENCRYPTION_NONE => Mage::helper('helpdesk')->__('NONE'),
            Mirasvit_Helpdesk_Model_Config::ENCRYPTION_SSL => Mage::helper('helpdesk')->__('SSL'),
            Mirasvit_Helpdesk_Model_Config::ENCRYPTION_TLS => Mage::helper('helpdesk')->__('TLS'),
            Mirasvit_Helpdesk_Model_Config::ENCRYPTION_TLSNOVALID => Mage::helper('helpdesk')->__('TLS (w/o validation)'),
        );
    }
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
