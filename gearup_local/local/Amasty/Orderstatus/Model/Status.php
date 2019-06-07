<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderstatus
 */
class Amasty_Orderstatus_Model_Status extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amorderstatus/status');
    }
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getAlias()) {
            $this->setAlias($this->_generateAlias($this->getStatus()));
        }
        return $this;
    }
    
    protected function _generateAlias($title)
    {
        $alias = trim(strtolower(preg_replace('@[^A-Za-z0-9_]@', '', $title)));
        if (strlen($alias) > 17) {
            $alias = substr($alias, 0, 17);
        }
        if (!$alias) {
            $alias = uniqid(rand(10, 99));
        }
        // need get unique alias
        $existStatuses = $this->getCollection();
        $existStatuses->addFieldToFilter('alias', $alias);
        while (0 < $existStatuses->getSize()) {
            unset($existStatuses);
            $alias = uniqid(rand(10, 99));
            $existStatuses = $this->getCollection();
            $existStatuses->addFieldToFilter('alias', $alias);
        }
        return $alias;
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        Mage::getModel('amorderstatus/status_template')->attachTemplates($this);
        return $this;
    }
}