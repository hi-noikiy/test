<?php
/**
 * 
 * Date: 23.10.15
 * Time: 12:24
 */
class Moogento_Pickpack_Helper_Flag extends Mage_Core_Helper_Abstract
{
    protected function getFlagValue($orderid = null,$flagname = null) {
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT '.$flagname.' FROM ' . $table . ' WHERE orderid = ' .(int)$orderid . ' LIMIT 1';
        $value = $readConnection->fetchOne($query);
        return $value;
    }

    protected function updateFlagValue($orderid = null,$flagname = null,$value = null) {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $newSku = 'new-sku';
        $query = "UPDATE {$table} SET {$flagname} = {$value} WHERE orderid = " . (int)$orderid;
        $writeConnection->query($query);
    }

    protected function updateFlagValues($orderid = null,$flagname1 = null,$value1 = null,$flagname2 = null,$value2 = null) {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $newSku = 'new-sku';
        $query = "UPDATE {$table} SET ({$flagname1} = {$value1},{$flagname2} = {$value2}) WHERE orderid = " . (int)$orderid;
        $writeConnection->query($query);
    }

    protected function insertFlagValue($orderid = null,$flagname = null,$value = null) {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $newSku = 'new-sku';
        $query = "INSERT INTO {$table} (orderid,{$flagname}) VALUES ({$orderid}, {$value})";
        $writeConnection->query($query);
    }

    protected function insertFlagValues($orderid = null,$flagname1 = null,$value1 = null,$flagname2 = null,$value2 = null) {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('moogento_pickpack_flagautoaction');
        $newSku = 'new-sku';
        $query = "INSERT INTO {$table} (orderid,{$flagname1},{$flagname2}) VALUES ({$orderid}, {$value1},{$value2})";
        $writeConnection->query($query);
    }
}