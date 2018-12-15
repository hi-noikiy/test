<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Eshopsync extends Mage_Core_Block_Template
{
	public function _prepareLayout()
 {
		return parent::_prepareLayout();
    }

     public function getEshopsync()
     {
        if (!$this->hasData('eshopsync')) {
            $this->setData('eshopsync', Mage::registry('eshopsync'));
        }
        return $this->getData('eshopsync');

    }
}
