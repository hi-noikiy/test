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



class Mirasvit_Fpc_Model_Observer_CmsSaveAfter extends Mirasvit_Fpc_Model_Observer_CacheClear
{
    /**
     * Flush cms page cache after cms save (if fpc cache in different folder)
     *
     * @param Varien_Event_Observer $e
     * @return bool
     */
    public function updateCache($e)
    {
        $fpcCacheDir = false;
        $options = Mage::app()->getConfig()->getNode('global/fpc');
        if (!$options) {
            return false;
        }

        $options = $options->asArray();

        if (isset($options['backend_options']['cache_dir'])
            && $options['backend_options']['cache_dir']) {
                $fpcCacheDir = $options['backend_options']['cache_dir'];
        }

        if ($fpcCacheDir) {
            $page = $e->getObject();
            $pageId = $page->getPageId();
            $tags = array(Mirasvit_Fpc_Model_Config::CMS_PAGE_TAG . $pageId);
            $this->_cache->clearCacheByTags($tags);
        }

        return true;
    }
}
