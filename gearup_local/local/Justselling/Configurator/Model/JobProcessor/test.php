<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_activemonitoragent
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * 
 * Start testing it by calling it from here with the debugger.
 * 
 */
require_once '../../../../../../Mage.php';

error_reporting(E_ALL);
umask(0);
Mage::app('');
$processor = new Justselling_Configurator_Model_Jobprocessor_Observer();
$processor->process();
?>