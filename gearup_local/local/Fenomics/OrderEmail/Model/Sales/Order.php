<?php

/**
 * FENOMICS extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Fenomics_OrderEmail module to newer versions in the future.
 * If you wish to customize the Fenomics GTM module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Fenomics
 * @package    Fenomics_OrderEmail
 * @copyright  Copyright (C) 2014 FENOMICS GmbH (http://www.fenomics.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * @category Fenomics
 * @package Fenomics_OrderEmail
 * @subpackage Helper
 * @author Wolfgang Embach <w.embach@fenomics.de>
 */

class Fenomics_OrderEmail_Model_Sales_Order extends Fenomics_OrderEmail_Model_Sales_Order_Amasty_Pure
{
    
    /**
     *
     * @return boolean
     */
    public function isOrderEmailenabled()
    {
        $w = Mage::getStoreConfigFlag('fe_orderemail_options/customization/fe_emailorder');
        return $w;
    }
    
    /**
     *
     * @return boolean
     */
    public function isEditEmailenabled()
    {
        $w = Mage::getStoreConfigFlag('fe_orderemail_options/customization/fe_edit_email');
        return $w;
    }
    
}
