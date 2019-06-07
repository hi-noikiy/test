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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

/**
 * @method int getId()
 * @method Justselling_Configurator_Model_Valuetagblacklist setId(int $value)
 * @method string getTag()
 * @method Justselling_Configurator_Model_Valuetagblacklist setTag(string $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Valuetagblacklist setOptionId(int $value)
 * @method int getOptionValueId()
 * @method Justselling_Configurator_Model_Valuetagblacklist setOptionValueId(int $value)
 * @method int getRelatedOptionId()
 * @method Justselling_Configurator_Model_Valuetagblacklist setRelatedOptionId(int $value)
 */

class Justselling_Configurator_Model_Valuetagblacklist extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/valuetagblacklist');
	}
}