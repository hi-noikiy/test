<?php
class EM_LayeredNavigation_Model_Resource_Catalog_Filter_Price extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{
	protected function _getSelect($filter) {
		$collection = $filter->getLayer()->getProductCollection();
		$collection->addPriceData($filter->getCustomerGroupId(), $filter->getWebsiteId());

		$select = clone $collection->getSelect();
		
		// reset columns, order and limitation conditions
		$select->reset(Zend_Db_Select::COLUMNS);
		$select->reset(Zend_Db_Select::ORDER);
		$select->reset(Zend_Db_Select::LIMIT_COUNT);
		$select->reset(Zend_Db_Select::LIMIT_OFFSET);

		// remove join with main table
		$fromPart = $select->getPart(Zend_Db_Select::FROM);
		if (!isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS])
			|| !isset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS])
		) {
			return $select;
		}

		// processing FROM part
		$priceIndexJoinPart = $fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS];
		$priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
		$priceIndexJoinPart['joinType'] = Zend_Db_Select::FROM;
		$priceIndexJoinPart['joinCondition'] = null;
		$fromPart[Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
		unset($fromPart[Mage_Catalog_Model_Resource_Product_Collection::INDEX_TABLE_ALIAS]);
		$select->setPart(Zend_Db_Select::FROM, $fromPart);
		foreach ($fromPart as $key => $fromJoinItem) {
			$fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
		}
		$select->setPart(Zend_Db_Select::FROM, $fromPart);

		// processing WHERE part
		$wherePart = $select->getPart(Zend_Db_Select::WHERE);
		foreach ($wherePart as $key => $wherePartItem) {
			$wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
		}
		$select->setPart(Zend_Db_Select::WHERE, $wherePart);
		$excludeJoinPart = Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.entity_id';
		foreach ($priceIndexJoinConditions as $condition) {
			if (strpos($condition, $excludeJoinPart) !== false) {
				continue;
			}
			$select->where($this->_replaceTableAlias($condition));
		}
		$select->where($this->_getPriceExpression($filter, $select) . ' IS NOT NULL');

		return $select;
	}
}
