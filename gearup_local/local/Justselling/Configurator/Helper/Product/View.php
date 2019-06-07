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
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Helper_Product_View extends Mage_Catalog_Helper_Product_View
{
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Catalog_Helper_Product_View::prepareAndRender()
	 */
	public function prepareAndRender($productId, $controller, $params = null)
	{
		if ($params->getSpecifyOptions()) {
			$params = null;
		}
				
		return parent::prepareAndRender($productId, $controller, $params);
	}
}