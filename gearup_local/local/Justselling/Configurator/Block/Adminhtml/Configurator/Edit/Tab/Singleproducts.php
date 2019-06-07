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

class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Singleproducts extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected function _prepareLayout() {
		
		$this->setChild ( 'generate_single_products', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array (
				'label' => Mage::helper ( 'catalog' )->__ ( 'Generate Single Products now' ),
				'class' => '',
				'id' => 'generate_single_products' ,
				'onclick' => 'confirmStartGenerate()'
		)));
		
		return parent::_prepareLayout();
	}
	
	/**
	 * Get tab label
	 *
	 * @return string
	 */
	public function getTabLabel() {
		return Mage::helper ( 'adminhtml' )->__ ( 'Single Products' );
	}
	
	/**
	 * Get tab title
	 *
	 * @return string
	 */
	public function getTabTitle() {
		return $this->getTabLabel ();
	}
	
	/**
	 * Whether tab is available
	 *
	 * @return bool
	 */
	public function canShowTab() {
		return true;
	}
	
	/**
	 * Whether tab is visible
	 *
	 * @return bool
	 */
	public function isHidden() {
		return false;
	}
	public function getTemplateId() {
		if (isset ( $this->_data ['id'] ))
			return $this->_data ['id'];
		return null;
	}
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct ();
		
		$this->setTemplate ( 'configurator/template/singleproducts.phtml' );
		$this->_data = Mage::registry ( "configurator_data" )->getData ();
	}
	
	/**
	 * Get Json Representation of Option/Value Tree
	 *
	 * @return string
	 */
	public function getResTreeJson() {
		$options = Mage::getModel ( "configurator/option" )->getCollection ();
		$options->addFieldToFilter ( "template_id", $this->getTemplateId () );
		
		$rootArray = array (
				'text' => "configuration",
				'sort_order' => '0',
				'id' => 'configuration',
				'children' => array () 
		);
		foreach ( $options as $option ) {
			$values = Mage::getModel ( "configurator/value" )->getCollection ();
			$values->addFieldToFilter ( "option_id", $option->getId () );
			$valueArray = array ();
			foreach ( $values as $value ) {
				$count = 0;
				$valueArray [] = array (
						'text' => $value->getTitle (),
						'sort_order' => $count ++,
						'id' => "value-".$value->getId (),
						'children' => array () 
				);
			}
			
			$rootArray ['children'] [] = array (
					'text' => $option->getTitle (),
					'sort_order' => $option->getSortOrder (),
					'id' => "option-".$option->getId (),
					'children' => $valueArray 
			);
		}
		
		$json = Mage::helper ( 'core' )->jsonEncode ( isset ( $rootArray ['children'] ) ? $rootArray ['children'] : array () );
		
		return $json;
	}
	
	protected function getLinkdedProductsSelectHtml()
	{
		$list = array();
	
		$options = Mage::getModel("configurator/template")->getLinkedProducts(Mage::registry("configurator_data")->getId());
		foreach ($options as $option_id) {
			$option = Mage::getModel("catalog/product_option")->load($option_id);
			$product = Mage::getModel("catalog/product")->load($option->getProductId());
			$list[$product->getId()] = $product->getName();
		}
	
		return $list;
	}
	
}
	