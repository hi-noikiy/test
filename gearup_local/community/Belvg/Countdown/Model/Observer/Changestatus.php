<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
/**********************************************
 *        MAGENTO EDITION USAGE NOTICE        *
 **********************************************/
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
/**********************************************
 *        DISCLAIMER                          *
 **********************************************/
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 **********************************************
 * @category   Belvg
 * @package    Belvg_Countdown
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

class Belvg_Countdown_Model_Observer_Changestatus
{
    /**
     * Enable/disable categories and products
     */
    public function changeStatus()
    {
        if (Mage::getStoreConfig('countdown/settings/enabled')==1) {
            $_model = Mage::getModel('countdown/countdown');

            $ids    = $_model->loadChangeOff('category');
            $_model->changeEntityEnabled($ids, 0, 'category');
            $_model->categoryEnabled($ids, 0);
            $ids    = $_model->loadChangeOn('category');
            $_model->changeEntityEnabled($ids, 1, 'category');
            $_model->categoryEnabled($ids, 1);

            $ids    = $_model->loadChangeOff('product');
            $_model->changeEntityEnabled($ids, 0, 'product');
            $_model->productEnabled($ids, 0);
            $ids    = $_model->loadChangeOn('product');
            $_model->changeEntityEnabled($ids, 1, 'product');
            $_model->productEnabled($ids, 1);

        }
    }

}
