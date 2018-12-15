<?php
class EM_LayeredNavigation_Model_Catalog_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
	public function getResetValue() {
		return null;
	}

	protected function _initItems() {
		// get root category id
		$rootId = Mage::app()->getStore()->getRootCategoryId();

		// get filtered category
		$curCat = $this->getCategory();
		if ($this->_appliedCategory && $this->_isValidCategory($this->_appliedCategory))
			$curCat = $this->_appliedCategory;

		$cat = $curCat;	// iterator object
		$items = array();
		do {
			if ($cat->getId()==$curCat->getId()) {
				// create item current category (recursively)
				$itemData = $this->_getCategoryItemData($cat);
				$item = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'], $itemData['items']);
				$item->setSelected(true);
			} else {
				// create item for ancestors
				$cat->setProductCount($this->_getCategoryProductCount($cat));
				$itemData = array(
					'label' => Mage::helper('core')->htmlEscape($cat->getName()),
					'value' => $cat->getId(),
					'count' => $cat->getProductCount(),
				);
				$item = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
				$item->setItems($items);
			}
			if ($cat->getId()==$rootId)
				$item->setLabel(Mage::helper('layerednavigation')->__('All Categories'));

			$items = array($item);
			if ($cat->getId()==$rootId) break;
			
			$parentId = $cat->getParentId();
			$cat = Mage::getModel('catalog/category');
			$cat->load($parentId);
		} while (true);
		
		$this->_items = $items;
		return $this;
	}
	
	/**
	 * Create filter item object with extra childs item for use in category tree
	 *
	 * @param array(itemData) $items
	 * @return Mage_Catalog_Model_Layer_Filter_Item
	 */
	protected function _createItem($label, $value, $count = 0, $items = array()) {
		$childItems = array();
		foreach ($items as $itemData)
			$childItems[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'], $itemData['items']);

		return Mage::getModel('catalog/layer_filter_item')
			->setFilter($this)
			->setLabel($label)
			->setValue($value)
			->setCount($count)
			->setData('items', $childItems);
	}
	
    /**
     * Get tree item for building category filter
     *
     * @return array
     */
	protected function _getCategoryItemData($cat) {
		$key  = $this->getLayer()->getStateKey() . '_CATEGORYITEM_' . $cat->getId();
		$data = $this->getLayer()->getAggregator()->getCacheData($key);

		if ($data === null) {
			$cat->setProductCount($this->_getCategoryProductCount($cat));
			$categories = $cat->getChildrenCategories();
			$childs = array();
			foreach ($categories as $category) {
				if ($category->getIsActive()) {
					$childs[] = $this->_getCategoryItemData($category);
				}
			}
			
			$data = array(
				'label' => Mage::helper('core')->htmlEscape($cat->getName()),
				'value' => $cat->getId(),
				'count' => $cat->getProductCount(),
				'items' => $childs
			);
		
			$tags = $this->getLayer()->getStateTags();
			$this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
		}
		return $data;
	}
	
	protected function _getCategoryProductCount($cat) {
		$result = 0;
		$select = clone $this->getLayer()->getProductCollection()->getSelect();
		$from = $select->getPart(Zend_Db_Select::FROM);

		// change category filter value
		if (isset($from['cat_index'])) {
			$condition = $from['cat_index']['joinCondition'];
			if(preg_match("/cat_index.category_id = '.*?'/",$condition))
				$condition = preg_replace("/cat_index.category_id = '.*?'/", "cat_index.category_id = '{$cat->getId()}'", $condition);
			else	
				$condition = preg_replace("/cat_index.category_id='.*?'/", "cat_index.category_id='{$cat->getId()}'", $condition);
			
			$notAnchor = 'AND cat_index.is_parent=1';
			if ($cat->getIsAnchor())
				$condition = str_replace($notAnchor, '', $condition);	// remove not anchor condition
			else
				$condition .= ' ' . $notAnchor;

			$from['cat_index']['joinCondition'] = $condition;
			$select->reset(Zend_Db_Select::ORDER);
			$select->reset(Zend_Db_Select::LIMIT_COUNT);
			$select->reset(Zend_Db_Select::LIMIT_OFFSET);
			$select->reset(Zend_Db_Select::COLUMNS);
			$select->setPart(Zend_Db_Select::FROM, $from);
			$select->columns(new Zend_Db_Expr("COUNT(*)"));
			
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$row = $connection->fetchRow($select);
			$result = reset($row);
		}

		return $result;
	}
}
