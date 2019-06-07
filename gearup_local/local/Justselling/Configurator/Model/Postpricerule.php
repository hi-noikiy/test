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
 * @method Justselling_Configurator_Model_Postpricerule setId(int $value)
 * @method string getTitle()
 * @method Justselling_Configurator_Model_Postpricerule setTitle(string $value)
 * @method int getOrder()
 * @method Justselling_Configurator_Model_Postpricerule setOrder(int $value)
 * @method string getPostPriceRule()
 * @method Justselling_Configurator_Model_Postpricerule setPostPriceRule(string $value)
 * @method int getTemplateId()
 * @method Justselling_Configurator_Model_Postpricerule setTemplateId(int $value)
 */

class Justselling_Configurator_Model_Postpricerule extends Mage_Core_Model_Abstract
{

	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/postpricerule');
	}

	public function saveTemplatePostpricerules(array $postpricerules)
	{		
		//Zend_Debug::dump($postpricerules); exit;
		 		
		foreach($postpricerules as $postpricerule) {			
			$postpriceruleModel = Mage::getModel("configurator/postpricerule")->load($postpricerule['id']);
			
			
			if( $postpriceruleModel->template_id != $this->getTemplate()->getId() ) {
				$postpriceruleModel = Mage::getModel("configurator/postpricerule");
			}
			
			if( $postpricerule['is_delete'] == "1" ) {
				$postpriceruleModel->delete();
			} else {				
				$postpriceruleModel->setTemplateId( $this->getTemplate()->getId() );
				$postpriceruleModel->setTitle( $postpricerule['title'] );
				$postpriceruleModel->setPostPriceRule( $postpricerule['post_price_rule'] );
				$postpriceruleModel->setOrder( $postpricerule['order'] );
				$result = $postpriceruleModel->save();
			}			
		}
	}
	
	public function getTemplatePostpricerules($templateId)
	{
		$collection = $this->getCollection();	
		$collection->addFilter('template_id',$templateId);		
		return $collection;
	}	
}