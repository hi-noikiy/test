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


class Mirasvit_Fpc_Model_System_Config_Source_UpdateCartMethod
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Mirasvit_Fpc_Model_Config::UPDATE_CART_METHOD_DEFAULT, 'label'=>Mage::helper('fpc')->__('Default')),
            array('value' => Mirasvit_Fpc_Model_Config::UPDATE_CART_METHOD_WHOLE_PAGE, 'label'=>Mage::helper('fpc')->__('Whole page')),
        );
    }
}
