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
namespace Magestore\Affiliateplus\Model\ResourceModel\Action;

/**
 * Collection Collection
 */
/**
 * Class Collection
 * @package Magestore\Affiliateplus\Model\ResourceModel\Action
 */
class Collection extends \Magestore\Affiliateplus\Model\ResourceModel\AbtractCollection
{
    /**
     * @var bool
     */
    protected $_customGroupSql = false;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplus\Model\Action','Magestore\Affiliateplus\Model\ResourceModel\Action');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCustomGroupSql($value) {
        $this->_customGroupSql = $value;
        return $this;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql() {
        if ($this->_customGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(\Zend_Db_Select::ORDER);
            $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(\Zend_Db_Select::COLUMNS);
            $countSelect->reset(\Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT referer, landing_page, store_id)');
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
}
