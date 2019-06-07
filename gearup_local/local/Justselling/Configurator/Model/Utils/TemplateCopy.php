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

class Justselling_Configurator_Model_Utils_TemplateCopy {

	/** @var $_objects Justselling_Configurator_Model_Export_TemplateObjects  */
	private $_templateObjects = null;

	/**
	 * Constructor.
	 *
	 * @param Justselling_Configurator_Model_Export_TemplateObjects $objects
	 */
	public function __construct(Justselling_Configurator_Model_Export_TemplateObjects $objects=null) {
		$this->_templateObjects = $objects;
	}

	/**
	 * Returns the model. Maybe an 'empty' or blank model will be returned if not found for the given ID.
	 * @param $name
	 * @param $id
	 * @return false|Mage_Core_Model_Abstract
	 */
	private function getModel($name, $id) {
		$model = null;
		if ($this->_templateObjects) {
			$model = $this->_templateObjects->getModel($name, $id);
		} else {
			$model = Mage::getModel($name)->load($id);
		}
		return $model;
	}

	/**
	 * Returns a collection for the given name and filter parameters. An array may be returned if the
	 * @param $name
	 * @param $fieldToFilter
	 * @param $filterValue
	 * @param $forceDb bool True in case the model is required to read from database
	 * @return array
	 */
	private function getCollection($name, $fieldToFilter=null, $filterValue=null, $forceDb=false) {
		if ($this->_templateObjects && !$forceDb) {
			$items = $this->_templateObjects->getAsArray($name);
			$collection = array();
			foreach ($items as $item) {
				if (is_null($fieldToFilter)) {
					$collection[] = $item;
				} else if ($item->getData($fieldToFilter) == $filterValue) {
					$collection[] = $item;
				}
			}
			return $collection;
		} else {
			/** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
			$collection = Mage::getModel($name)->getCollection();
			if (!is_null($fieldToFilter)) {
				$collection->addFieldToFilter($fieldToFilter, $filterValue);
			}
			$items = $collection->getItems();
			return $items;
		}
	}


	/**
	 * Strips the given data elements by reducing it to only that ones specified in the given table. This
	 * avoids any crashes which belong to fields added by any of the customers.
	 * @param array $data
	 * @param $tableName
	 * @param $db Magento_Db_Adapter_Pdo_Mysql
	 * @return array
	 */
	protected function _prepareColumns(array $data, $tableName, $db) {
		$columns = $db->describeTable($tableName);
		$result = array_intersect_key($data, $columns);
		return $result;
	}

	/**
	 * @param $modelName string
	 * @param $data array
	 * @param $db Magento_Db_Adapter_Pdo_Mysql
	 */
	protected function _insertData($modelName, $data, $db) {
		/* @var $resource Mage_Core_Model_Resource */
		$resource = Mage::getSingleton('core/resource');
		$data = $this->_prepareColumns($data, $resource->getTableName($modelName), $db);
		$tableName = $resource->getTableName($modelName);
		$affectedRows = $db->insertArray($tableName, array_keys($data), array($data));
		if (!$affectedRows) {
			Js_Log::log("Insertion didn't affect any row: ", $this, Zend_Log::WARN);
		}
	}


	/**
	 * Copies a template on the source of a template for the given ID. The template source may be a persisted one stored
	 * in database or, depending on the constructor of this entity, a file-based, imported one.
	 *
	 * @param $id template ID, never null
	 * @return int templateID the new generated template ID
	 * @throws Exception on any internal exception
	 */
	public function copy($id) {
		if (is_null($id)) throw new Mage_Exception("Template ID to copy should never be null!");

		/* @var $resource Mage_Core_Model_Resource */
		$resource = Mage::getSingleton('core/resource');
		/* @var $db Magento_Db_Adapter_Pdo_Mysql */
		$db = $resource->getConnection('core_write');
		$db->exec("SET FOREIGN_KEY_CHECKS=0");
		$db->beginTransaction();
		try {
			$templateModel = $this->getModel('configurator/template', $id);
			$templateId = $templateModel->getId();
			if (!$templateId) throw new Mage_Exception("Could not read template for ID {$id}!");

			$newTemplateId = NULL;
			$data = $templateModel->getData();
			$data['title'] = (isset($this->_templateObjects) ? '' : 'Copy of ').$data['title'];
			unset($data['id']);

			$this->_insertData('configurator/template', $data, $db);
			$templateTable = Mage::getSingleton("core/resource")->getTableName('configurator/template');
			$newTemplateId = $db->fetchOne("SELECT id FROM $templateTable ORDER BY id DESC");
			if( !$newTemplateId ) throw new Exception("Could not create new Template Entry!");

			/** @var $newTemplate Justselling_Configurator_Model_Template */
			$newTemplate = Mage::getModel('configurator/template')->load($newTemplateId);
			$newTemplate->adjustImageReferences(true);

			$options = $this->getCollection("configurator/option", 'template_id', $templateId);
			$groups = array();

			// Mapping Arrays
			$group_mapping = array();
			$optionValueIdMapping = array();
			$optionMapping = array();

			$groupsCollection = $this->getCollection("configurator/optiongroup", 'template_id', $templateId);
			foreach ($groupsCollection as $group) {
					$oldGroupId = $group->getId();
					$group->setId ( NULL );

					// Copy groups of the options and build array to map old
					// to new group-ids
					$newGroup = Mage::getModel ( "configurator/optiongroup" );
					$newGroup->setData ($group->getData());
					$newGroup->setTemplateId ($newTemplateId);
					$newGroup->save();
					$newGroup->adjustImageReferences(true);
					$group_mapping[$oldGroupId] = $newGroup->getId();
			}

			//$q = "SELECT * FROM configurator_option WHERE template_id = $templateId;";
			//$result = $db->query($q);
			$options = $this->getCollection("configurator/option", 'template_id', $templateId);
			if(count($options)) {
				//$configOpts = $result->fetchAll(PDO::FETCH_ASSOC);
				$configOpts = $options;
				/* @var $object Mage_Core_Model_Abstract */
				foreach($configOpts as $key => $object) {
					$tmp = $object->getData();
					$oldOptionId = $tmp['id'];
					unset($tmp['id']);
					unset($tmp['parent_id']);
					$tmp['template_id'] = $newTemplateId;
					if (isset($group_mapping[$tmp["option_group_id"]]))
						$tmp['option_group_id'] = $group_mapping[$tmp["option_group_id"]];

					// add Option
					$this->_insertData('configurator/option', $tmp, $db);
					$optionTable = Mage::getSingleton("core/resource")->getTableName('configurator/option');
					$newOptionId = (int) $db->fetchOne("SELECT id FROM $optionTable ORDER BY id DESC LIMIT 1");
					$configOpts[$key]['new_id'] = $newOptionId;
					$optionMapping[$oldOptionId] = $newOptionId;

					// Copy Matrixvalue of the option
					$matrixvalues = $this->getCollection("configurator/optionmatrix", 'option_id', $oldOptionId);
					foreach ($matrixvalues as $matrixvalue) {
						$oldMatrixvalueId = $matrixvalue->getId();
						$matrixvalue->setId(Null);

						$newMatrixValue = Mage::getModel("configurator/optionmatrix");
						$newMatrixValue->setData($matrixvalue->getData());
						$newMatrixValue->setOptionId($newOptionId);
						$newMatrixValue->save();
					}

					// Copy Option Font
					$fonts = $this->getCollection("configurator/optionfont", 'option_id', $oldOptionId);
					foreach ($fonts as $font) {
						$oldFontId = $font->getId();
						$font->setId(Null);

						$newFont = Mage::getModel("configurator/optionfont");
						$newFont->setData($font->getData());
						$newFont->setOptionId($newOptionId);
						$newFont->save();
					}

					// Copy Option Font Color
					$fontcolors = $this->getCollection("configurator/optionfontcolor", 'option_id', $oldOptionId);
					foreach ($fontcolors as $fontcolor) {
						$oldFontcolorId = $fontcolor->getId();
						$fontcolor->setId(Null);

						$newFontColor = Mage::getModel("configurator/optionfontcolor");
						$newFontColor->setData($fontcolor->getData());
						$newFontColor->setOptionId($newOptionId);
						$newFontColor->save();
					}

					// Copy Option Font Configuration
					$fontconfs = $this->getCollection("configurator/optionfontconfiguration", 'option_id', $oldOptionId);
					foreach ($fontconfs as $fontconf) {
						$oldFontcolorId = $fontconf->getId();
						$fontconf->setId(Null);

						$newFontconf = Mage::getModel("configurator/optionfontconfiguration");
						$newFontconf->setData($fontconf->getData());
						$newFontconf->setOptionId($newOptionId);
						$newFontconf->save();
					}

					// Copy Option Font Position
					$fontposs = $this->getCollection("configurator/optionfontposition", 'option_id', $oldOptionId);
					foreach ($fontposs as $fontpos) {
						$oldFontposrId = $fontpos->getId();
						$fontpos->setId(Null);

						$newFontpos = Mage::getModel("configurator/optionfontposition");
						$newFontpos->setData($fontpos->getData());
						$newFontpos->setOptionId($newOptionId);
						$newFontpos->save();
					}

					// pricelist
					//$resultPl = $db->query("SELECT * FROM configurator_pricelist WHERE option_id = $oldOptionId;");
					$configPricelist = $this->getCollection("configurator/pricelist", 'option_id', $oldOptionId);
					foreach($configPricelist as $keyPl => $itemObject) {
						$pl = $itemObject->getData();
						unset($pl['id']);
						$pl['option_id']  = $newOptionId;
						$this->_insertData('configurator/pricelist', $pl, $db);
					}

					// option value
					//$resultOv = $db->query("SELECT * FROM configurator_option_value WHERE option_id = $oldOptionId;");
					//if( $resultOv ) {
					//$configOv = $resultOv->fetchAll(PDO::FETCH_ASSOC);

					$optionValueItems = $this->getCollection("configurator/value", 'option_id', $oldOptionId);
					foreach($optionValueItems as $keyOv => $itemObject) {
						$ov = $itemObject->getData();
						$oldValueId = $ov['id'];
						unset($ov['id']);
						$ov['option_id']  = $newOptionId;
						$this->_insertData('configurator/option_value', $ov, $db); // !!! option_value
						$optionValueTable = Mage::getSingleton("core/resource")->getTableName('configurator/option_value');
						$newOvId = (int) $db->fetchOne("SELECT id FROM $optionValueTable ORDER BY id DESC LIMIT 1");
						$configOv[$keyOv]['new_id'] = $newOvId;

						$newOptionValue = Mage::getModel('configurator/value')->load($newOvId);
						$newOptionValue->setTemplate($newTemplate);
						$newOptionValue->adjustImageReferences(true);

						// Create Mapping Array for old and new Value-Ids
						$optionValueIdMapping[$oldValueId] = $newOvId;

						// Copy Option Value Tags
						$valueTags = $this->getCollection("configurator/valuetag", 'option_value_id', $oldValueId);
						foreach ($valueTags as $valueTag) {
							$valueTag->setId(Null);

							$newValuetag = Mage::getModel("configurator/valuetag");
							$newValuetag->setData($valueTag->getData());
							$newValuetag->setOptionValueId($newOvId);
							$newValuetag->save();
						}
					}

				}


				/*// option parent_id fix
				if( isset($configOpts) && count($configOpts) > 0 ) {
					foreach($configOpts as $key => $itemObject) {
						$tmp = $itemObject->getData();
						foreach($configOpts as $key2 => $itemObject2) {
							$tmp2 = $itemObject2;
							if( $tmp['parent_id'] == $tmp2['id'] ) {
								$db->update("configurator_option", array('parent_id' => $tmp2['new_id']),'id = '.$tmp['new_id']);
								$configOpts[$key]['new_parent_id'] = $tmp2['new_id'];
								break;
							}
						}
					}
				}*/

				// Adapt pricelist-value
				//$pricelistvalues = Mage::getModel("configurator/pricelistvalue")->getCollection();
				$priceListValues = $this->getCollection('configurator/pricelistvalue');
				foreach ($priceListValues as $oldItem) {
					if (in_array($oldItem->getOptionValueId(), array_keys($optionValueIdMapping))) {
						$oldItem->setId ( Null );
						$newItem = Mage::getModel ("configurator/pricelistvalue");
						$newItem->setData ( $oldItem->getData () );
						$newItem->setOptionValueId ( $optionValueIdMapping [$oldItem->getOptionValueId ()] );
						$newItem->save ();
					}
				}

				// Create option-blacklist items
				$optionBlacklistEntries = $this->getCollection("configurator/optionblacklist");
				foreach ($optionBlacklistEntries as $oldItem) {
					if (in_array($oldItem->getOptionId(), array_keys($optionMapping))) {
						$oldItem->setId ( Null );
						$newItem = Mage::getModel ( "configurator/optionblacklist" );
						$newItem->setData ( $oldItem->getData () );
						$newItem->setChildOptionValueId ( $optionValueIdMapping [$oldItem->getChildOptionValueId ()] );
						$newItem->setOptionId ( $optionMapping [$oldItem->getOptionId ()] );
						$newItem->save ();
					}
				}

				// Copy Blacklist Values
				$blackListValues = $this->getCollection("configurator/blacklist");
				foreach ($blackListValues as $blackListValue) {
					if (in_array($blackListValue->getOptionValueId(), array_keys($optionValueIdMapping))) {
						$blackListValue->setId(Null);
						$newBlacklistvalue = Mage::getModel("configurator/blacklist");
						$newBlacklistvalue->setData($blackListValue->getData());
						$newBlacklistvalue->setOptionValueId($optionValueIdMapping[$blackListValue->getOptionValueId()]);
						if ($blackListValue->getChildOptionValueId()) {
							$newBlacklistvalue->setChildOptionValueId($optionValueIdMapping[$blackListValue->getChildOptionValueId()]);
						}
						if ($blackListValue->getChildOptionId()) {
							$newBlacklistvalue->setChildOptionId($optionMapping[$blackListValue->getChildOptionId()]);
						}
						$newBlacklistvalue->save();
					}
				}

				// Create option-valuetag-blacklist items
				$optionValueTagBlacklistEntries = $this->getCollection("configurator/valuetagblacklist");
				foreach ($optionValueTagBlacklistEntries as $oldItem) {
					if (in_array($oldItem->getOptionId(), array_keys($optionMapping))) {
						$oldItem->setId ( Null );
						$newItem = Mage::getModel ( "configurator/valuetagblacklist" );
						$newItem->setData ( $oldItem->getData () );
						$newItem->setOptionValueId ( $optionValueIdMapping [$oldItem->getOptionValueId ()] );
						$newItem->setOptionId ( $optionMapping [$oldItem->getOptionId ()] );
						$newItem->setRelatedOptionId ( $optionMapping [$oldItem->getRelatedOptionId ()] );
						$newItem->save ();
					}
				}

				// Adapt child option status
				$statuscollection = $this->getCollection("configurator/childoptionstatus");
				foreach ($statuscollection as $status) {
					if (in_array($status->getOptionId(), array_keys($optionMapping))) {
						$status->setId(Null);
						$newStatus = Mage::getModel("configurator/childoptionstatus");
						$newStatus->setData($status->getData());
						$newStatus->setOptionId($optionMapping[$status->getOptionId()]);
						if (array_key_exists($status->getChildOptionId(), $optionMapping)) {
							/* Bugfix: we ignore entries with child option Id referencing to another option */
							$newStatus->setChildOptionId($optionMapping[$status->getChildOptionId()]);
							$newStatus->save();
						}
					}
				}

				/* Adapt expressions to new option-ids */
				/* Attention: we're editing an existing (already persisted) option here! */
				$options = $this->getCollection("configurator/option", 'template_id', $newTemplateId, true);
				/** @var $option Justselling_Configurator_Model_Option */
				foreach ($options as $option) {
					$doUpdate = false;

					/* Update/Set default values + parent_id if set in source option */
					$srcOptionId = $this->getSourceOptionIdForOptionId($optionMapping, $option->getId());
					if ($srcOptionId) {
						$srcOption = $this->getModel('configurator/option', $srcOptionId);
						if ($srcOption) {
							if ($srcValueId = $srcOption->getDefaultValue()) {
								if (array_key_exists($srcValueId, $optionValueIdMapping)) {
									$newValueId = $optionValueIdMapping[$srcValueId];
									$option->setDefaultValue($newValueId);
									$doUpdate = true;
								}
							}
							if ($srcParentId = $srcOption->getParentId()) {
								if (array_key_exists($srcParentId, $optionMapping)) {
									$newParentId = $optionMapping[$srcParentId];
									$option->setParentId($newParentId);
									$doUpdate = true;
								}
							}
						}
					}

					/* Adjust/Correct image references */
					if ($option->adjustImageReferences()) {
						$doUpdate = true;
					}

					/* Adapt options in matrixvalue dimensions */
					/* Attention: we're editing an existing (already persisted) option here! */
					if ($option->getType() == "matrixvalue") {
						$operator_x = $option->getMatrixDimensionX();
						preg_match_all("/opt[0-9]+/", $operator_x, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "opt".$id;
							$replace = "opt".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$operator_x = str_replace($search, $replace, $operator_x);
						}
						preg_match_all("/price[0-9]+/", $operator_x, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "price".$id;
							$replace = "price".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$operator_x = str_replace($search, $replace, $operator_x);
						}
						$option->setMatrixDimensionX($operator_x);

						$operator_y = $option->getMatrixDimensionY();
						preg_match_all("/opt[0-9]+/", $operator_y, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "opt".$id;
							$replace = "opt".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$operator_y = str_replace($search, $replace, $operator_y);
						}
						preg_match_all("/price[0-9]+/", $operator_y, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "price".$id;
							$replace = "price".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$operator_y = str_replace($search, $replace, $operator_y);
						}
						$option->setMatrixDimensionY($operator_y);
						$doUpdate = true;
					}

					/* Update the expressions regarding to the old option IDs */
					if ($option->getType() == "expression") {
						$expression = $option->getExpression();
						preg_match_all("/opt[0-9]+/", $expression, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "opt".$id;
							$replace = "OPT".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$expression = preg_replace(array('/'. $search .'/'), $replace, $expression, 1 );
						}
						$expression = str_replace('OPT', 'opt', $expression);

						preg_match_all("/price[0-9]+/", $expression, $matches);
						foreach ($matches[0] as $match) {
							preg_match("/[0-9]+/", $match, $num);
							$id = $num[0];
							$search = "price".$id;
							$replace = "PRICE".$optionMapping[$id];
							if (isset($optionMapping[$id]))
								$expression = preg_replace(array('/'. $search .'/'), $replace, $expression, 1 );
						}
						$expression = str_replace('PRICE', 'price', $expression);

						$option->setExpression($expression);
						$doUpdate = true;
					}

					if ($doUpdate) {
						$option->save();
					}

					/* Update the _selectcombi expressions regarding to the old option IDs */
					if ($option->getType() == "selectcombi" || $option->getType() == "listimagecombi" || $option->getType() == "listimagecombi" || $option->getType() == "overlayimagecombi") {
						$expression = $option->getSelectcombiExpression();
						if($expression){
							preg_match_all("/opt[0-9]+/", $expression, $matches);
							foreach ($matches[0] as $match) {
								preg_match("/[0-9]+/", $match, $num);
								$id = $num[0];
								$search = "opt".$id;
								$replace = "OPT".$optionMapping[$id];
								if (isset($optionMapping[$id]))
									$expression = preg_replace(array('/'. $search .'/'), $replace, $expression, 1 );
							}
							$expression = str_replace('OPT', 'opt', $expression);

							preg_match_all("/price[0-9]+/", $expression, $matches);
							foreach ($matches[0] as $match) {
								preg_match("/[0-9]+/", $match, $num);
								$id = $num[0];
								$search = "price".$id;
								$replace = "PRICE".$optionMapping[$id];
								if (isset($optionMapping[$id]))
									$expression = preg_replace(array('/'. $search .'/'), $replace, $expression, 1 );
							}
							$expression = str_replace('PRICE', 'price', $expression);

							$option->setSelectcombiExpression($expression);
							$doUpdate = true;
						}
					}

					if ($doUpdate) {
						$option->save();
					}
				}


				// Adapt post price rules
				$postpricerules = $this->getCollection("configurator/postpricerule", 'template_id', $templateId);
				foreach ($postpricerules as $rule) {
					$expression = $rule->getPostPriceRule ();
					preg_match_all ( "/price[0-9]+/", $expression, $matches );
					foreach ( $matches [0] as $match ) {
						preg_match ( "/[0-9]+/", $match, $num );
						$id = $num [0];
						$search = "price" . $id;
						$replace = "price" . $optionMapping [$id];
						if (isset ( $optionMapping [$id] ))
							$expression = str_replace ( $search, $replace, $expression );
					}

					$rule->setId ( Null );
					$newRule = Mage::getModel ( "configurator/postpricerule" );
					$newRule->setData ( $rule->getData () );
					$newRule->setTemplateId($newTemplateId);
					$newRule->setPostPriceRule ( $expression );
					$newRule->save ();
				}

				// Adapt  rules
				$rules = $this->getCollection("configurator/rules", 'template_id', $templateId);
				foreach ($rules as $rule) {
					$rule->setId ( Null );
					$newRule = Mage::getModel("configurator/rules");
					$newRule->setData($rule->getData());
					$newRule->setTemplateId($newTemplateId);

					$oldOptionId = $rule->getOptionId();
					if(isset($oldOptionId)){
						$newRule->setOptionId($optionMapping[$oldOptionId]);
					}

					$oldScope = $rule->getScope();
					if(isset($oldScope) && is_numeric($oldScope)){
						$newRule->setScope($group_mapping[$oldScope]);
					}

					$newRule->save();
				}

				//$db->rollback(); Zend_Debug::dump($configOpts); exit;
			}

			$db->commit();
			//$db->rollback();

			$db->exec("SET FOREIGN_KEY_CHECKS=1");
			return $newTemplateId;

		} catch (Exception $e) {
			Js_Log::logException($e, $this, 'Copy of template failed!');
			$db->rollback();
			$db->exec("SET FOREIGN_KEY_CHECKS=0");
			throw $e;
		}
	}

	/**
	 * Returns the source(=old) option ID for the given (new) option ID, false if not found.
	 * @param array $optionMapping
	 * @param $optionId
	 * @return int|bool the old option ID for the given (new) option ID, false if new could not be found
	 */
	private function getSourceOptionIdForOptionId(array $optionMapping, $optionId) {
		foreach ($optionMapping as $srcOptionId => $newOptionId) {
			if ($newOptionId == $optionId) return $srcOptionId;
		}
		return false;
	}

}