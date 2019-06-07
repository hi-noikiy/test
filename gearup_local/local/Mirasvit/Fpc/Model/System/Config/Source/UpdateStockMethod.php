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
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_Fpc_Model_System_Config_Source_UpdateStockMethod
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $updateStockMethod = array(
            array('value' => Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_DEFAULT, 'label'=>Mage::helper('fpc')->__('Default')),
            array('value' => Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_FRONTEND, 'label'=>Mage::helper('fpc')->__('Default (check stock in frontend)')),
        );

        if (!Mage::helper('mstcore')->isModuleInstalled('Mirasvit_AsyncIndex')) {
            $updateStockMethod[] = array('value' => Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_REINDEX, 'label'=>Mage::helper('fpc')->__('Update during reindex (Cache Level should be Default)'));
            $updateStockMethod[] = array('value' => Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_FULL_REINDEX, 'label'=>Mage::helper('fpc')->__('Update during full reindex (Cache Level should be Default)'));
        }

        return $updateStockMethod;
    }
}
