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
 * @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

/**
 * @method string getBody()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setBody(string $value)
 * @method string getSessionId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setSessionId(string $value)
 * @method int getProductId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setProductId(int $value)
 * @method string getStatus()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setStatus(string $value)
 * @method string getJsTemplateId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setJsTemplateId(string $value)
 * @method int getWidth()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setWidth(int $value)
 * @method int getTemplateId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setTemplateId(int $value)
 * @method int getOptionId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setOptionId(int $value)
 * @method int getOrderId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setOrderId(int $value)
 * @method int getQuoteItemId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setQuoteItemId(int $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setId(int $value)
 * @method string getContent()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setContent(string $value)
 * @method int getOrderItemId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setOrderItemId(int $value)
 * @method int getHeight()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setHeight(int $value)
 * @method int getQuoteId()
 * @method Justselling_Configurator_Model_Vectorgraphics_File setQuoteId(int $value)
 */

class Justselling_Configurator_Model_Vectorgraphics_File extends Mage_Core_Model_Abstract
{

    const STATUS_CREATED         	    = 0;
    const STATUS_ASSIGNED_TO_QUOTE      = 1;
    const STATUS_ASSIGNED_TO_ORDER      = 2;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('configurator/vectorgraphics_file');
    }


}
