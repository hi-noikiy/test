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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method int getId()
 * @method Justselling_Configurator_Model_Childoptionstatus setId(int $value)
 * @method int getChildOptionId()
 * @method Justselling_Configurator_Model_Childoptionstatus setChildOptionId(int $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Childoptionstatus setStatus(string $value)
 * @method int getIsCombi()
 * @method Justselling_Configurator_Model_Childoptionstatus setIsCombi(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Childoptionstatus setOptionId(int $value)
 */
class Justselling_Configurator_Model_Childoptionstatus extends Mage_Core_Model_Abstract
{
	 /* @var Justselling_Configurator_Model_Template */
	protected $_template;
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/childoptionstatus');
	}
}