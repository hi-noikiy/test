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
 * */
class Gearup_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Options extends Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Options {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('gearup/configurator/template/options.phtml');
    }

    public function getRecommendedSelectHtml() {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
                ->setData(array(
                    'id' => $this->getFieldId() . '_${option_id}_select_${id}_is_recommended',
                    'class' => 'select select-product-option-is_recommended'
                ))
                ->setName($this->getFieldName() . '[${option_id}][values][${id}][is_recommended]')
                ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

}
