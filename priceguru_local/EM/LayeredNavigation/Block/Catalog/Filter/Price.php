<?php
class EM_LayeredNavigation_Block_Catalog_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price
{
	/**
	 * Price expression, used in retrieve price in database
	 *
	 * @var string
	 */
	protected $_priceExpression = null;
	
	/**
	 * Additional sql use in price calculation
	 *
	 * @var string
	 */
	protected $_additionalPriceExpression = null;
	
	/**
	 * Currency rate
	 *
	 * @var float
	 */
	protected $_currencyRate = null;

	public function __construct() {
		parent::__construct();
		$this->_filterModelName = 'layerednavigation/catalog_filter_price';
	}

	/**
	 * Retrive price's statistic: min price, max price, price slider from/to
	 *
	 * @return array()
	 */
	public function getRangeStatistic() {
		$select = clone $this->getLayer()->getProductCollection()->getSelect();

		// reset select
		$select->reset(Zend_Db_Select::ORDER);
		$select->reset(Zend_Db_Select::LIMIT_COUNT);
		$select->reset(Zend_Db_Select::LIMIT_OFFSET);
		$select->reset(Zend_Db_Select::COLUMNS);
		$select->reset(Zend_Db_Select::DISTINCT);
		$select->reset(Zend_Db_Select::WHERE);

		// get min/max price
		$this->_preparePriceExpressionParameters($select);
		$priceExpression = $this->_priceExpression . ' ' . $this->_additionalPriceExpression;
        $sqlEndPart = ') * ' . $this->_currencyRate . ', 2)';
		$select->columns('ROUND(MAX(' . $priceExpression . $sqlEndPart);
		$select->columns('ROUND(MIN(' . $priceExpression . $sqlEndPart);
		$select->where($this->_priceExpression . ' IS NOT NULL');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $row = $connection->fetchRow($select, array(), Zend_Db::FETCH_NUM);
		$to = $maxPrice = ceil((float)$row[0]);
		$from = $minPrice = floor((float)$row[1]);
        
		// get current price interval
		$interval = $this->_filter->getInterval();
		if (!empty($interval)) {
			$from = max($minPrice, $interval[0]);
			$to = min($maxPrice, $interval[1]);
		}
		
		$result = array(
			'min_price' => $minPrice,
			'max_price' => $maxPrice,
			'from' => $from,
			'to' => $to
		);
		return $result;
	}
	
	/**
	 * Prepare price expression use in sql string
	 *
	 * @return EM_LayeredNavigation_Block_Catalog_Filter_Price
	 */
	protected function _preparePriceExpressionParameters($select) {
        // prepare response object for event
        $response = new Varien_Object();
        $response->setAdditionalCalculations(array());
        $tableAliases = array_keys($select->getPart(Zend_Db_Select::FROM));
        if (in_array('price_index', $tableAliases)) {
            $table = 'price_index';
        } else {
            $table = reset($tableAliases);
        }
		
        // prepare event arguments
		$storeId = Mage::app()->getStore()->getId();
        $eventArgs = array(
            'select'          => $select,
            'table'           => $table,
            'store_id'        => $storeId,
            'response_object' => $response
        );

        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);
        $additional = join('', $response->getAdditionalCalculations());

        $this->_priceExpression = $table . '.min_price';
		$this->_additionalPriceExpression = $additional;
		$this->_currencyRate = Mage::app()->getStore($storeId)->getCurrentCurrencyRate();
        return $this;
    }
	
	/**
	 * Retrieve query string for price filter
	 *
	 * @return string
	 */
	public function getQueryTemplate() {
		parse_str($_SERVER['QUERY_STRING'], $params);
		$params[$this->_filter->getRequestVar()] = '_from_-_to_';
		$query = '?'.http_build_query($params);
		return $query;
	}
	
	/**
	 * Retrieve price format string
	 *
	 * @return string
	 */
	public function getValueTemplate() {
		$price = 123;	// sample price used to get price template
		$formatted = Mage::app()->getStore()->formatPrice($price);
		return str_replace($price, '_value_', $formatted);
	}
}
