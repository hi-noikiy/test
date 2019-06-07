<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        $searchBlocks = array();
        $searchBlocks[] = Mage::getConfig()->getBlockClassName('catalogsearch/result');
        $searchBlocks[] = Mage::getConfig()->getBlockClassName('catalogsearch/advanced_result');
        $searchBlocks[] = Mage::getConfig()->getBlockClassName('searchindex/results'); // compatibility with the `Mirasvit: Search Index` extension
        if (in_array(get_class($block), $searchBlocks)) {
            $defaultSorting = Mage::getStoreConfig('amsorting/default_sorting/search');
            $direction = ($defaultSorting == 'relevance') ? 'desc' : 'asc';
            
            $block->getChild('search_result_list')
                ->setDefaultDirection($direction)
                ->setSortBy($defaultSorting);
                
            Mage::unregister(Amasty_Sorting_Block_Catalog_Product_List_Toolbar::SEARCH_SORTING);
            Mage::register(Amasty_Sorting_Block_Catalog_Product_List_Toolbar::SEARCH_SORTING, true);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function settingsChanged(Varien_Event_Observer $observer)
    {
        if ('amsorting' == $observer->getObject()->getSection()) {
            $settings = $observer->getObject()->getData();
            if ($settings['groups']['general']['fields']['use_index']
                && !Mage::getStoreConfig('amsorting/general/use_index')) {
                $indexer = Mage::getSingleton('index/indexer')
                    ->getProcessByCode('amsorting_summary');
                if ($indexer) {
                    $indexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                }
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function syncCategoryDefaultSorting(Varien_Event_Observer $observer)
    {
        $linkFields = array('amsorting/default_sorting/category', 'catalog/frontend/default_sort_by');
        $configData = $observer->getEvent()->getConfigData();
        if ($configData && $configData->isValueChanged()) {
            if (in_array($configData->getPath(), $linkFields)) {
                $syncField = $configData->getPath() == $linkFields[0] ? $linkFields[1] : $linkFields[0];
                Mage::getConfig()->saveConfig(
                    $syncField, $configData->getValue(), $configData->getScope(), $configData->getScopeId()
                );
            }
        }

        return $this;
    }
}
