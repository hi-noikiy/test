<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_tickets
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */

/**
 * @method int getId()
 * @method Justselling_Configurator_Model_Singleproduct_Job setId(int $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Singleproduct_Job setStatus(string $value)
 * @method string getConfig()
 * @method Justselling_Configurator_Model_Singleproduct_Job setConfig(string $value)
 * @method int getTemplateId()
 * @method Justselling_Configurator_Model_Singleproduct_Job setTemplateId(int $value)
 * @method int getJobId()
 * @method Justselling_Configurator_Model_Singleproduct_Job setJobId(int $value)
 */

class Justselling_Configurator_Model_Singleproduct_Job extends Mage_Core_Model_Abstract {
    
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/singleproduct_job');
	}
	
}