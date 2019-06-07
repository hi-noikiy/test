<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Minification_QueueProcessor
{
    const MAX_FILE_COUNT_PER_STEP = 100;

    private $fails = 0;

    public function process()
    {
        $tasks = Mage::getResourceModel('amoptimization/task_collection')
            ->setPageSize(self::MAX_FILE_COUNT_PER_STEP)
            ->load();

        foreach ($tasks as $task) {
            $success = $task->process();
            if ($success) {
                $task->delete();
            } else {
                $this->fails++;
            }
        }
        if (Mage::app()->getStore()->isAdmin()) {
            Mage::getSingleton('adminhtml/session')->setMinificationFails($this->fails);
        }
    }
}
