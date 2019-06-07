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



class Mirasvit_Fpc_Model_Observer_System_Status extends Varien_Debug
{
    /**
     * @var Mirasvit_Fpc_Helper_Data
     */
    protected $_fpcHelper;

    public function __construct()
    {
        $this->_fpcHelper = Mage::helper('fpc');
    }

    /**
     * Check for fpc section in admin panel.
     */
    public function checkStatus()
    {
        if (($request = Mage::app()->getRequest())
            && (Mage::app()->getRequest()->getParam('section') == 'fpc'
                || Mage::app()->getRequest()->getParam('section') == 'fpccrawler')
            && !$request->isPost()
        ) {
            $isShowOptimalConfigurationInfo = true;
            $cronStatus = $this->_fpcHelper->showCronStatusError();
            $freeHddSpace = $this->_fpcHelper->showFreeHddSpace(false, false);

            if ((is_string($freeHddSpace) && strlen($freeHddSpace) > 20)
                || (is_string($cronStatus) && strlen($cronStatus) > 20)) {
                $isShowOptimalConfigurationInfo = false;
            }

            if ($isShowOptimalConfigurationInfo
                && Mage::getModel('core/variable')->loadByCode(Mirasvit_Fpc_Model_Config::OPTIMAL_CONFIG_MESSAGE)->getValue()) {
                $this->showOptimalConfigurationInfo();
            }

            if (Mage::helper('mstcore')->isModuleInstalled('Xtento_StockImport')
                && Mage::getSingleton('fpc/config')->getCacheTagslevelLevel() != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_FIRST) {
                Mage::getSingleton('adminhtml/session')->addError('Xtento_StockImport is installed.
                    For Full Page Caching to update stock properly, please set "Cache Level" to "Default"
                    and do one of the following:
                    1) Set Catalog->Stock Import->Import Profiles->Import Settings->"Reindex mode"
                    to "Full reindex (after import)" or 2) Set FPC > Configuration > "Update stock method"
                    to "Update during full reindex". Please note: second solution will require manual reindex
                    of "Stock Status" after import using Xtento_StockImport extension.');
            }

            if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_AsyncIndex')
                && Mage::getSingleton('fpc/config')->getCacheTagslevelLevel() != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_FIRST) {
                Mage::getSingleton('adminhtml/session')->addError('Mirasvit_AsyncIndex installed. "Cache Level" should be "Default".');
            } elseif (Mage::getSingleton('fpc/config')->getCacheTagslevelLevel() != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_FIRST
                    && (Mage::getSingleton('fpc/config')->getUpdateStockMethod() == Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_REINDEX
                        || Mage::getSingleton('fpc/config')->getUpdateStockMethod() == Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_FULL_REINDEX)) {
                Mage::getSingleton('adminhtml/session')
                        ->addError('If "Update stock method" set to "Update during reindex" or "Update during full reindex" '
                        .'than "Cache Level" should be "Default".');
            }
        }
    }

    /**
     * Show an advice after FPC install.
     */
    protected function showOptimalConfigurationInfo()
    {
        $script = '<script type="text/javascript">function callFpcHideMessageController(e)
        {
            var isChecked = (e.checked == true) ? 1 : 0;
            new Ajax.Request("'.Mage::helper('adminhtml')->getUrl('*/fpc_hideMessage/update').'" , {
                method: "Post",
                parameters: {"checked":isChecked},
                // onComplete: function(transport) {
                //     alert(transport.responseText);
                // }
            });
        } </script>';

        $urlOptimalConfig = Mage::helper('adminhtml')->getUrl('*/fpc_optimalConfiguration/index/');
        $urlCrawlerConfig = Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/fpccrawler');
        $fpcVersion = Mage::helper('fpc/version')->getCurrentFpcVersion();

        $documetation = 'More info about the FPC extension you can learn follow the link <a target="_blank" href="https://docs.mirasvit.com/doc/extension_fpc/current/">"Getting Started"</a>';

        $info = '<div>It is a good idea check "Suggest Optimal Configuration" ( <a target="_blank" href="'
            .$urlOptimalConfig
            .'">System->Full Page Cache->Suggest Optimal Configuration</a> ) and push "Generate crawler urls" in "Full Page Cache Crawler" ( <a target="_blank" href="'
            .$urlCrawlerConfig
            .'">System->Full Page Cache->Crawler Settings</a> )'
            .'<br/>'
            .$documetation
            .'<br/>'
            .'Don\'t show again <input onchange="callFpcHideMessageController(this)" type="checkbox" name="fpc_hide_message" class="massaction-checkbox"></div>'
            .$script
            ;

        Mage::getSingleton('adminhtml/session')->addNotice($info);
    }
}
