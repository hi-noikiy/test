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



class Mirasvit_Fpc_Model_System_Config_Source_MobileDetectOptions
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('fpc')->__('Disabled')),
            array('value' => Mirasvit_Fpc_Model_Config::MOBILE_DETECT_MOBILE,
                'label'=>Mage::helper('fpc')->__('Mobile cache for mobile')
            ),
            array('value' => Mirasvit_Fpc_Model_Config::MOBILE_DETECT_MOBILE_TABLET,
                'label'=>Mage::helper('fpc')->__('Mobile cache for mobile and tablet') 
            ),
        );
    }
}
