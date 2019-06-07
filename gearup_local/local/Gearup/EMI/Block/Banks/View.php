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
 * Bank view block
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Banks_View extends Mage_Core_Block_Template
{
    /**
     * get the current bank
     *
     * @access public
     * @return mixed (Gearup_EMI_Model_Banks|null)
     * @author Ultimate Module Creator
     */
    public function getCurrentBanks()
    {
        return Mage::registry('current_banks');
    }
}
