<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


$this->startSetup();
$table = $this->getTable('core/config_data');
$move = array(
    'amsorting/general/best_period' => 'amsorting/bestsellers/best_period',
    'amsorting/general/best_attr' => 'amsorting/bestsellers/best_attr',
    'amsorting/general/exclude' => 'amsorting/bestsellers/exclude',
    'amsorting/general/viewed_period' => 'amsorting/most_viewed/viewed_period',
    'amsorting/general/viewed_attr' => 'amsorting/most_viewed/viewed_attr',
    'amsorting/general/new_attr' => 'amsorting/newest/new_attr',
    'amsorting/general/saving' => 'amsorting/biggest_saving/saving'
);
$query = '';
foreach ($move as $oldPath => $newPath) {
    $query .= "UPDATE `$table` SET `path` = '" . $newPath . "' WHERE `path` = '" . $oldPath . "'; ";
}

$this->run($query);

$this->endSetup();
