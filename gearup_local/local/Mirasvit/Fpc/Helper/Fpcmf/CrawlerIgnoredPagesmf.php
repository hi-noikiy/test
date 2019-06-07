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



class Mirasvit_Fpc_Helper_Fpcmf_CrawlerIgnoredPagesmf extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isCrawlerIgnoredPage()
    {
        $regExps = $this->getCrawlerConfig()->getCrawlerIgnoredPages();
        $datamf = new Mirasvit_Fpc_Helper_Fpcmf_Datamf();
        $normUrl = $datamf->getNormlizedUrl();
        foreach ($regExps as $exp) {
            if ($this->_validateRegExp($exp) && preg_match($exp, $normUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function _validateRegExp($exp)
    {
        if (@preg_match($exp, null) === false) {
            return false;
        }

        return true;
    }

    public function getCrawlerConfig()
    {
        $fpcCrawlerConfig = new Mirasvit_Fpc_Model_Configmf();
        return $fpcCrawlerConfig;
    }
}
