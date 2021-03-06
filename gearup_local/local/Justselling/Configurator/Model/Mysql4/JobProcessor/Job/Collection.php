<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Model_Mysql4_Jobprocessor_Job_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	/**
	 * @see Mage_Core_Model_Mysql4_Collection_Abstract::_construct()
	 */
	public function _construct() {
		$this->_init('configurator/jobprocessor_job');
	}

	/**
	 * @see Varien_Data_Collection::toOptionArray()
	 */
	public function toOptionArray($key = 'id') {
		return $this->_toOptionArray($key);
	}
}