<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block;

/**
 * @category Magestore
 * @package  Magestore_Affiliateplus
 * @module   Affiliateplus
 * @author   Magestore Developer
 */
class Grid extends AbstractTemplate
{
    protected $_columns = array();

    /**
     * Grid's Collection
     */
    protected $_collection;
    protected $_filter_value;
    /**
     * @return array
     */
    public function getColumns(){
        return $this->_columns;
    }

    /**
     * @param $collection
     * @return $this
     */
    public function setCollection($collection){
        $this->_collection = $collection;

        if (!$this->getData('add_searchable_row')) {
            return $this;
        }
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset($column['searchable']) && $column['searchable']) {
                if (isset($column['filter_function']) && $column['filter_function']) {
                    $this->fetchFilter($column['filter_function']);
                } else {
                    $field = isset($column['index']) ? $column['index'] : $columnId;
                    $field = isset($column['filter_index']) ? $column['filter_index'] : $field;
                    if ($filterValue = $this->getFilterValue($columnId)) {
                        $this->_collection->addFieldToFilter($field, ['like' => "%$filterValue%"]);
                    }
                    if ($filterValue = $this->getFilterValue($columnId, '-from')) {
                        if ($column['type'] == 'price') {
                            $store = $this->_storeManager->getStore();
                            $filterValue /= $store->getBaseCurrency()->convert(1, $store->getCurrentCurrency());
                        } elseif ($column['type'] == 'date' || $column['type'] == 'datetime') {
                            $filterValue = date('Y-m-d', strtotime($filterValue));
                        }
                        $this->_collection->addFieldToFilter($field, ['gteq' => $filterValue]);
                    }

                    if ($filterValue = $this->getFilterValue($columnId, '-to')) {
                        if ($column['type'] == 'price') {
                            $store = $this->_storeManager->getStore();
                            $filterValue /= $store->getBaseCurrency()->convert(1, $store->getCurrentCurrency());
                        } elseif ($column['type'] == 'date' || $column['type'] == 'datetime') {
                            $filterValue = date('Y-m-d', strtotime($filterValue)+86400);
                        }
                        $this->_collection->addFieldToFilter($field, ['lteq' => $filterValue]);
                    }

                }
            }

        }
        return $this;
    }

    public function getFilterValue($columnId = null, $offset = '') {
        if (!$this->hasData('filter_value')) {
            if ($filter = $this->getRequest()->getParam('filter')) {
                $filter = urldecode(base64_decode($filter));;
                parse_str($filter, $filter);
            }
            $this->setData('filter_value', $filter);
        }
        if (is_null($columnId)) {
            return $this->getData('filter_value');
        } else {
            return $this->getData('filter_value/' . $columnId . $offset);
        }

    }

    /**
     * fetch filter custom function
     *
     * @param string $parentFuction
     * @return mixed
     */
    public function fetchFilter($parentFuction) {
        $parentBlock = $this->getParentBlock();
        return $parentBlock->$parentFuction($this->_collection, $this->getFilterValue());
    }

    /**
     * @return mixed
     */
    public function getFilterUrl() {
        if (!$this->hasData('filter_url')) {
            $this->setData('filter_url', $this->getUrl('*/*/*'));
        }
        return $this->getData('filter_url');
    }

    /**
     * @return string
     */
    public function getPagerHtml() {
        if ($this->getData('add_searchable_row')) {
            return $this->getParentBlock()->getPagerHtml();
        }
        return '';
    }

    /**
     * @return mixed
     */
    public function getCollection(){
        return $this->_collection;
    }

    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::grid.phtml');
        return $this;
    }

    /**
     * @param $columnId
     * @param $params
     * @return $this
     */
    public function addColumn($columnId, $params){
        if (isset($params['searchable']) && $params['searchable']) {
            $this->setData('add_searchable_row', true);
            if (isset($params['type']) &&
                ($params['type'] == 'date' || $params['type'] == 'datetime')
            ) {
                $this->setData('add_calendar_js_to_grid', true);
            }
        }
        $this->_columns[$columnId] = $params;
        return $this;
    }

    /**
     * @param $parentFunction
     * @param $row
     * @return mixed
     */
    public function fetchRender($parentFunction, $row){
        $parentBlock = $this->getParentBlock();

        $fetchObj = new \Magento\Framework\DataObject(
            [
                'function'	=> $parentFunction,
                'html'		=> false,
            ]
        );
        $this->_eventManager->dispatch("affiliateplus_grid_fetch_render_$parentFunction",
            [
                'block'	=> $parentBlock,
                'row'	=> $row,
                'fetch'	=> $fetchObj,
            ]
        );

        if ($fetchObj->getHtml())
            return $fetchObj->getHtml();

        return $parentBlock->$parentFunction($row);
    }
    /**
     * @return mixed
     */
    public  function getBaseUrl(){
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param null $date
     * @param int $format
     * @param bool|false $showTime
     * @param null $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    )
    {
        return parent::formatDate($date, $format, $showTime, $timezone); // TODO: Change the autogenerated stub
    }

    /**
     * @param $value
     * @param bool|true $format
     * @return float
     */
    public function convertPrice($value, $format = true)
    {
        return parent::convertPrice($value, $format); // TODO: Change the autogenerated stub
    }
}
