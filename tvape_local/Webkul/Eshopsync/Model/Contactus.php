<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Contactus extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/contactus');
    }

    public function contactusMapping($data)
    {
		if(isset($data['created_by'])){
			$created_by = $data['created_by'];
		}else{
			$helper = Mage::helper('eshopsync/connection');
			$created_by = $helper::$magento_user;
		}
		$this->setName($data['name']);
		$this->setEmail($data['email']);
		$this->setPhone($data['telephone']);
		$this->setComment($data['comment']);
		$this->setSforceId($data['sforce_id']);
		$this->setCreatedBy($created_by);
		$this->save();
	}
}
