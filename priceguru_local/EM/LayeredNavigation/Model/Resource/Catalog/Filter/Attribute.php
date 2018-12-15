<?php
class EM_LayeredNavigation_Model_Resource_Catalog_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
	public function applyFilterToCollection($filter, $value) {
		//modify input value for use in multiselect
		$value = str_replace('_', ',', $value);

		$collection = $filter->getLayer()->getProductCollection();
		$attribute  = $filter->getAttributeModel();
		$connection = $this->_getReadAdapter();
		$tableAlias = $attribute->getAttributeCode() . '_idx';
		$conditions = array(
			"{$tableAlias}.entity_id = e.entity_id",
			$connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
			$connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
			"{$tableAlias}.value IN ({$value})"	// use IN collection for multiselect
		);
		
		$collection->getSelect()->distinct(true);

		$collection->getSelect()->join(array(
			$tableAlias => $this->getMainTable()
		), implode(' AND ', $conditions), array());
		return $this;
	}
	
	public function getCount($filter) {
		// clone select from collection with filters
		$select = clone $filter->getLayer()->getProductCollection()->getSelect();
		// reset columns, order and limitation conditions
		$select->reset(Zend_Db_Select::COLUMNS);
		$select->reset(Zend_Db_Select::ORDER);
		$select->reset(Zend_Db_Select::LIMIT_COUNT);
		$select->reset(Zend_Db_Select::LIMIT_OFFSET);
		
		$connection = $this->_getReadAdapter();
		$attribute  = $filter->getAttributeModel();
		$tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
		$conditions = array(
			"{$tableAlias}.entity_id = e.entity_id",
			$connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
			$connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId())
		);
		
		// exclude attribute join part from select
		$from = $select->getPart(Zend_Db_Select::FROM);
		unset($from[$tableAlias]);
		$select->setPart(Zend_Db_Select::FROM, $from);
		
		$select->join(array(
			$tableAlias => $this->getMainTable()
		), join(' AND ', $conditions), array(
			'value',
			'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")
		))->group("{$tableAlias}.value");
		
		return $connection->fetchPairs($select);
	}
}
