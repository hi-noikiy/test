<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Questions
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Questions base helper
 *
 * @category   Mage
 * @package    Mage_Questions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class EM_Emailofproduct_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ENABLED   = 'emailofproduct/emailofproduct/enabled';
	const XML_PATH_BANK_NAME   = 'bankinfo/bank_details/bank_name';
	const XML_PATH_BANK_ACC_NAME   = 'bankinfo/bank_details/account_name';
	const XML_PATH_BANK_ACC_NUM   = 'bankinfo/bank_details/account_num';	

    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_ENABLED );
    }

    public function getUserName()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return trim("{$customer->getFirstname()} {$customer->getLastname()}");
    }

    public function getUserEmail()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getEmail();
    }
	
    public function getBankName()
    {
        return Mage::getStoreConfig( self::XML_PATH_BANK_NAME );
    }	
	
    public function getBankAccName()
    {
        return Mage::getStoreConfig( self::XML_PATH_BANK_ACC_NAME );
    }		
	
    public function getBankAccNumber()
    {
        return Mage::getStoreConfig( self::XML_PATH_BANK_ACC_NUM );
    }		
}