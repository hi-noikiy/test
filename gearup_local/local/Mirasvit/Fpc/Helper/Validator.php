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



class Mirasvit_Fpc_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    public function testMirasvitCrc()
    {
        $modules = array('Fpc');

        return Mage::helper('mstcore/validator_crc')->testMirasvitCrc($modules);
    }

    public function testPhpConfiguration()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Check PHP configuration';
        $description = array();

        $memoryLimit = ini_get('memory_limit');
        $memoryLimit = (int) $memoryLimit;
        if ($memoryLimit < 256 && $memoryLimit != -1) {
            $description[] = 'You need to increase memory limit to at least 256M (512M or higher is recommended). Current value is '.$memoryLimit.'.';
            $result = self::FAILED;
        }

        return array($result, $title, $description);
    }

    public function testTablesExists()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Required tables exist';
        $description = array();

        $tables = array(
            'fpc/log',
            'fpc/log_aggregated_daily',
        );

        foreach ($tables as $table) {
            if (!$this->dbTableExists($table)) {
                $description[] = "Table '$table' does not exist";
                $result = self::FAILED;
            }
        }

        return array($result, $title, $description);
    }

    public function testConflicts()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Conflicts';
        $description = array();

        if (Mage::helper('mstcore')->isModuleInstalled('Devinc_Gomobile')) {
            $result = self::FAILED;
            $description[] = 'Devinc Gomobile installed. If you see folowing code "Mage::app()->getCacheInstance()->flush();"';
            $description[] = 'in file /app/code/community/Devinc/Gomobile/Model/Observer.php, please comment out it and contact to developers of the extension or disable this extension.';
            $description[] = "This code periodically flush cache, so Full Page Cache can't work correctly.";
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Lesti_Fpc')) {
            $result = self::FAILED;
            $description[] = 'Lesti Fpc installed. Please, disable the extension in file /app/etc/modules/Lesti_Fpc.xml. Then flush all cache.';
            $description[] = "Full Page Cache can't work correctly with Lesti Fpc installed.";
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Emagicone_Mobassistantconnector')) {
            $result = self::FAILED;
            $description[] = 'Extension Emagicone Mobassistantconnector installed. If FPC flush cache very often without visible reason the reason can be in Emagicone_Mobassistantconnector extension.';
            $description[] = 'To fix the issue in file /app/code/community/Emagicone/Mobassistantconnector/controllers/IndexController.php comment line  Mage::app()->cleanCache();';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Aitoc_Aitsys')) {
            $result = self::FAILED;
            $description[] = "Extension Aitoc_Aitsys installed. If FPC don't cache pages without visible reason the reason can be in Aitoc_Aitsys extension.";
            $description[] = 'To fix the issue in file /app/code/community/Aitoc/Aitsys/Abstract/Service.php comment line $this->getCache()->flush();';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Softag_Powerdash')) {
            $result = self::FAILED;
            $description[] = 'Extension Softag_Powerdash installed. If FPC flush cache very often without visible reason the reason can be in Softag_Powerdash extension.';
            $description[] = 'To fix the issue in file /app/code/community/Softag/Powerdash/Helper/Data.php comment line Mage::app()->getCache()->clean(\'all\', array(self::CACHE_TAG));';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Mci_Core')) {
            $result = self::FAILED;
            $description[] = "Extension Mci_Core installed. If FPC don't cache pages without visible reason the reason can be in Mci_Core extension.";
            $description[] = 'To fix the issue in file //app/code/community/Mci/Core/Model/Observer.php comment line _967976c690de23ce4d148bc33b3fb384(true);';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('GoMage_Procart')) {
            $result = self::FAILED;
            $description[] = 'Extension GoMage_Procart installed. To update cart correctly you need do
                <a href="https://docs.mirasvit.com/doc/extension_fpc/current/faq#gomage_procart_compatibility">following steps</a>';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Cmsmart_Megamenu')) {
            $result = self::FAILED;
            $description[] = 'Extension Cmsmart_Megamenu installed. FPC crawler can work incorrectly.';
            $description[] = 'To fix the issue in file /app/code/local/Cmsmart/Megamenu/Block/Navigation.php delete first empty line before php tag.';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('MailUp_MailUpSync')) {
            $result = self::FAILED;
            $description[] = 'Extension MailUp_MailUpSync installed. If FPC flush cache without visible reason the reason can be in MailUp_MailUpSync extension.';
            $description[] = 'To fix the issue in file /app/code/local/MailUp/MailUpSync/Model comment line Mage::app()->cleanCache();';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Netreviews_Avisverifies')) {
            $result = self::FAILED;
            $description[] = 'Extension Netreviews_Avisverifies installed. If FPC flush cache without visible reason the reason can be in Netreviews_Avisverifies extension.';
            $description[] = 'To fix the issue in file /app/code/local/Netreviews/Avisverifies/controllers/DialogController.php comment lines Mage::app()->cleanCache();';
        }

        return array($result, $title, $description);
    }

    public function testSimilarExtensions()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Conflicts with similar extensions';
        $description = array();

        $modules = array_keys((array) Mage::getConfig()->getNode('modules')->children());

        foreach ($modules as $module) {
            if (stripos($module, 'fpc') !== false && $module != 'Mirasvit_Fpc' && $module != 'Mirasvit_FpcCrawler') {
                $result = self::FAILED;
                $description[] = "Another FPC extension '$module' installed, please remove it.";
            }
        }

        return array($result, $title, $description);
    }

    public function testCompatibility()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Compatibility with extensions';
        $description = array();

        if (Mage::helper('mstcore')->isModuleInstalled('Simple_Forum')) {
            $result = self::INFO;
            $description[] = 'If you want cache forum page add in System->Configuration->Full Page Cache->Cachable Actions: forum/topic_index, forum/topic_view, forum/index_index.';
            $description[] = "And in System->Configuration->Full Page Cache->Ignored Pages: /forum/\like/\like/";
            $description[] = 'To enable autoflush when user like post or add post comment out code <br/>
             $content = Mage::helper(\'fpc/simpleforum\')->prepareContent($content);<br/>
             and<br/>
             if ($topicCacheId = Mage::helper(\'fpc/simpleforum\')->getSimpleForumCacheId()) {<br/>
             &nbsp;&nbsp;&nbsp;   $this->_requestId .= $del . $topicCacheId;<br/>
             }<br/>
             in file /app/code/local/Mirasvit/Fpc/Model/Processor.php (Simple_Forum extension compatibility).
             ';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Jn2_Cache')) {
            $result = self::INFO;
            $description[] = "You use magento 'JN2 solution'. Add /cache-get=/ in Ignored Pages ( System->Full Page Cache->Settings ).";
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Xtento_StockImport')) {
            $result = self::INFO;
            $description[] = 'Xtento_StockImport is installed.
                    For Full Page Caching to update stock properly, please set "Cache Level" to "Default"
                    and do one of the following:
                    1) Set Catalog->Stock Import->Import Profiles->Import Settings->"Reindex mode"
                    to "Full reindex (after import)" or 2) Set FPC > Configuration > "Update stock method"
                    to "Update during full reindex". Please note: second solution will require manual reindex
                    of "Stock Status" after import using Xtento_StockImport extension.';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Action')) {
            $result = self::INFO;
            $description[] = 'To exclude Mirasvit Promotional Offers timer from FPC cache you need uncomment block ';
            $description[] = 'action_view_timer (action/link)
             in file /app/code/local/Mirasvit/Fpc/etc/cache.xml.
             ';
        }

        if (Mage::helper('mstcore')->isModuleInstalled('WP_SmartColumns')
            && $this->isBlockExcluded('catalog_product_list', 'catalog/product_list', 'product_list')) {
            $result = self::INFO;
            $description[] = 'WP_SmartColumns is installed. To exclude \'catalog/product_list\'
                block you need change in file /app/code/community/WP/SmartColumns/Block/Toolbar.php
                private function _getLayoutFileName() line "$template = $this->getLayout()->getBlock(\'root\')->getTemplate();"
                at "if (Mage::registry(\'current_category_id\')) {
                    $template = [direct_template_value];
                } else {
                    $template = $this->getLayout()->getBlock(\'root\')->getTemplate();
                }"
                [direct_template_value] you will see if temporarily write "echo $template"
                ([direct_template_value] can be \'page/3columns.phtml\' or similar).';
        }

        return array($result, $title, $description);
    }

    public function testBugs()
    {
        $result = self::SUCCESS;
        $title = 'FPC: Current version errors';
        $description = array();

        if (($version = Mage::helper('fpc/version')->getExtensionVersion(true))
            && ($description = $this->getErrorDescription($version))) {
            $result = self::INFO;
        }

        return array($result, $title, $description);
    }

    private function getErrorDescription($version)
    {
        $description = array();
        $version = $this->prepareVersion($version);

        if ($version <= $this->prepareVersion('1.0.1.316')) {
            $description[] = 'Error with excluding reports/product_viewed block. Fixed in new version (file app/code/local/Mirasvit/Fpc/Model/Container/Productviewed.php)';
        }

        if ($version > $this->prepareVersion('1.0.3.0') && $version < $this->prepareVersion('1.0.3.477')) {
            $description[] = '"Last crawler job run time" incorrect info. Fixed in new version.';
            $description[] = 'For manual fix change in file /app/code/local/Mirasvit/FpcCrawler/controllers/Adminhtml/Fpccrawler/UrlController.php $this->_getLastCronTime(\'fpccrawler\') at  $this->_getLastCronTime(\'fpc_crawler\')';
            $description[] = 'and in file /app/code/local/Mirasvit/FpcCrawler/controllers/Adminhtml/Fpccrawlerlogged/UrlController.php $this->_getLastCronTime(\'fpccrawlerlogged\') at  $this->_getLastCronTime(\'fpc_crawlerlogged\')';
        }

        return $description;
    }

    private function prepareVersion($version)
    {
        //fix for new versions
        $explodedVersion = explode('.', $version);
        if (isset($explodedVersion[3]) && $explodedVersion[3] == 0) {
            return str_replace('.', '', $version) * 10;
        }

        return str_replace('.', '', $version);
    }

    /**
     * @param string $block
     * @param string $name
     *
     * @return bool
     */
    private function isBlockExcluded($blockContainer, $block, $name)
    {
        $customConfig = Mage::getConfig()->loadModulesConfiguration('custom.xml');
        $customConfigNode = $customConfig->getNode();
        $customConfigNodeAsArray = $customConfigNode->asArray();
        if ($customConfigNodeAsArray
            && isset($customConfigNodeAsArray['containers'][$blockContainer])
            && isset($customConfigNodeAsArray['containers'][$blockContainer]['block'])
            && isset($customConfigNodeAsArray['containers'][$blockContainer]['name'])
            && ($customConfigNodeAsArray['containers'][$blockContainer]['block'] == $block)
            && ($customConfigNodeAsArray['containers'][$blockContainer]['name'] == $name)
        ) {
            return true;
        }

        return false;
    }
}
