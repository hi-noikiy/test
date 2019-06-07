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



class Mirasvit_Fpc_Block_Adminhtml_Flushcache extends Mage_Adminhtml_Block_Template
{
    public function getStoresData()
    {
        $storesData = array();
        $stores = Mage::app()->getStores();

        foreach ($stores as $store)
        {
            if ($store->getIsActive()) {
                $storesData[$store->getId()] = $store;
            }
        }

        return $storesData;
    }

    /**
     * @return array
     */
    public function getCacheableActions()
    {
        $cacheableActionsData = array();
        $cacheableActions = $this->getConfig()->getCacheableActions();


        foreach ($cacheableActions as $action) {
            switch ($action) {
                case 'cms/index_index':
                    $cacheableActionsData[] = new Varien_Object(array(
                        'name' => 'Home page ( ' . $action . ' )',
                        'action' => $action,
                    ));
                    break;
                case 'cms/page_view':
                    $cacheableActionsData[] = new Varien_Object(array(
                        'name' => 'CMS pages ( ' . $action . ' )',
                        'action' => $action,
                    ));
                    break;

                case 'catalog/category_view':
                    $cacheableActionsData[] = new Varien_Object(array(
                        'name' => 'Category pages ( ' . $action . ' )',
                        'action' => $action,
                    ));
                    break;

                case 'catalog/product_view':
                    $cacheableActionsData[] = new Varien_Object(array(
                        'name' => 'Product pages ( ' . $action . ' )',
                        'action' => $action,
                    ));
                    break;

                default:
                    $cacheableActionsData[] = new Varien_Object(array(
                        'name' => $action,
                        'action' => $action,
                    ));
                    break;
            }
        }

        return $cacheableActionsData;
    }

    public function getConfig()
    {
        return Mage::getSingleton('fpc/config');
    }

}
