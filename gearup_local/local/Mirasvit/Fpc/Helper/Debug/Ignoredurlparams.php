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



class Mirasvit_Fpc_Helper_Debug_Ignoredurlparams extends Mage_Core_Helper_Abstract
{
    /**
     * Return applied ignored parameters
     * @param array $ignoredUrlParams
     * @return array
     */
    public function isIgnoredUrlParamApplied($ignoredUrlParams)
    {
        $appliedIgnoredParams = array();
        $getParams = array_keys($_GET);
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        foreach ($ignoredUrlParams as $param) {
            if ((in_array($param, $getParams) || $param == Mirasvit_Fpc_Model_Config::FIRST_PAGE)
                && strpos($currentUrl, $param) !== false) {
                $appliedIgnoredParams[] = $param;
            }
        }

        $appliedIgnoredParams = implode(', ', $appliedIgnoredParams);

        return $appliedIgnoredParams;
    }
}
