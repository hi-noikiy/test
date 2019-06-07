<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
interface Justselling_Configurator_Model_Jobprocessor_Processor {
    
    /**
     * Returns true if everything is valid with the given configuration.
     * 
     * @param array $params
     */
    public function isValid($params);
    
    /**
     * Returns the total number of items to process.
     * 
     * @param array Any key/values pairs
     */
    public function getTotalToProcess($params);
    
    /**
     * Preforms the process.
     * 
     * @param array $params Job Parameters, as default always containing: 'runtime_max' => seconds
     * @param Justselling_Configurator_Model_Jobprocessor_Callback $callback
     */
    public function process($params, Justselling_Configurator_Model_Jobprocessor_Callback $callback);
    
    
    /**
     * Performs any finalize tasks.
     * 
     * @param array $params 
     */
    public function finalize($params);
}
?>