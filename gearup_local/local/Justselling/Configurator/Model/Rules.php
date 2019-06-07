<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

/**
 * @method string getMessage()
 * @method Justselling_Configurator_Model_Rules setMessage(string $value)
 * @method int getId()
 * @method Justselling_Configurator_Model_Rules setId(int $value)
 * @method string getScope()
 * @method Justselling_Configurator_Model_Rules setScope(string $value)
 * @method string getWhenExecuted()
 * @method Justselling_Configurator_Model_Rules setWhenExecuted(string $value)
 * @method int getValue()
 * @method Justselling_Configurator_Model_Rules setValue(int $value)
 * @method int getTemplateId()
 * @method Justselling_Configurator_Model_Rules setTemplateId(int $value)
 * @method string getOperatorvalue()
 * @method Justselling_Configurator_Model_Rules setOperatorvalue(string $value)
 * @method string getAppliedfor()
 * @method Justselling_Configurator_Model_Rules setAppliedfor(string $value)
 */
class Justselling_Configurator_Model_Rules extends Mage_Core_Model_Abstract {


	protected function _construct(){
		parent::_construct();
		$this->_init('configurator/rules');
	}


	public function saveTemplateRules(array $rules)
	{
		foreach($rules as $rule) {
			$model = Mage::getModel("configurator/rules")->load($rule['id']);

			if( $model->getTemplateId() != $this->getTemplate()->getId() ) {
				$model = Mage::getModel("configurator/rules");
			}

			if( $rule['is_delete'] == "1" ) {
				$model->delete();
			} else {
				$model->setTemplateId( $this->getTemplate()->getId() );
				$model->setScope($rule['scope']);
				$model->setAppliedfor($rule['appliedfor']);
				$model->setOperatorvalue($rule['operatorvalue']);
				$model->setValue($rule['value']);
				$model->setMessage($rule['message']);
				$model->setWhenExecuted($rule['when_executed']);
				$model->setOptionId($rule['option_id']);

				$model->save();
			}
		}
	}

}