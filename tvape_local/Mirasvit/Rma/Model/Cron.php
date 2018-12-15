<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Cron
{

    /**
     * Main cron execution routine
     *
     * @return void
     */
    public function run()
    {
        $this->executeCronRules();
    }

    /**
     * Fires event Check Every Hour for all RMA's and starts cron-based rules processing
     *
     * @return void
     */
    public function executeCronRules()
    {
        $rmas = Mage::getModel('rma/rma')->getCollection();
        foreach ($rmas as $rma) {
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_CRON_HOUR_CHECK, $rma);
        }
    }


}