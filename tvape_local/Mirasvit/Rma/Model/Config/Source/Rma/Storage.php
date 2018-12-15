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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Config_Source_Rma_Storage
{

    /**
     * @return array
     */
    public function toArray()
    {
        $options = array(
            Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_DATABASE => Mage::helper('rma')->__('Database'),
            Mirasvit_Rma_Model_Config::ATTACHMENT_STORAGE_FS => Mage::helper('rma')->__('File System'),
        );

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }


}