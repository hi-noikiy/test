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



class Mirasvit_Rma_Model_Config_Source_Is_Archived
{
    /**
     * @param bool|false $emptyOption
     *
     * @return array
     */
    public function toArray($emptyOption = false)
    {
        $result = array();
        if ($emptyOption) {
            $result[0] = Mage::helper('rma')->__('-- Please Select --');
        }

        $result[Mirasvit_Rma_Model_Config::IS_ARCHIVE_TO_ARCHIVE] = Mage::helper('rma')->__('Move to Archive');
        $result[Mirasvit_Rma_Model_Config::IS_ARCHIVE_FROM_ARCHIVE] = Mage::helper('rma')->__('Move from Archive');

        return $result;
    }

    /**
     * @param bool|false $emptyOption
     *
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $result = array();
        foreach ($this->toArray($emptyOption) as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
