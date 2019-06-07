<?php 
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Bank helper
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Helper_Banks extends Mage_Core_Helper_Abstract
{

    /**
     * get the url to the banks manager  list page
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getBankssUrl()
    {
        if ($listKey = Mage::getStoreConfig('gearup_emi/banks/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('gearup_emi/banks/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author Ultimate Module Creator
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('gearup_emi/banks/breadcrumbs');
    }
}
