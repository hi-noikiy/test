<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


$this->startSetup();
$table = $this->getTable('core/config_data');
$this->run("UPDATE `$table` SET `path` = 'amsorting/default_sorting/search' WHERE `path` = 'amsorting/general/default_search'");

$select = $this->getConnection()->select()
    ->from($table, array('scope', 'scope_id', 'value'))
    ->where('`path` = "catalog/frontend/default_sort_by"');
$magentoConfig = $this->getConnection()->fetchAll($select);
foreach ($magentoConfig as $value) {
    $insertSelect = "
INSERT IGNORE INTO `$table` (`scope`, `scope_id`, `path`, `value`) 
VALUES ('{$value['scope']}', '{$value['scope_id']}', 'amsorting/default_sorting/category', '{$value['value']}')
";
    $this->run($insertSelect);
}

$this->endSetup();
