<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Status extends Varien_Object	{

	const STATUS_ENABLED	= 'yes';
    const STATUS_DISABLED	= 'no';

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('eshopsync')->__('Yes'),
            self::STATUS_DISABLED   => Mage::helper('eshopsync')->__('No')
        );
    }

}
