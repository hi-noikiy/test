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
 * @package   mirasvit/extension_fpc
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



/**
 * Контроллер для запуска кравлера (что бы все действия производились под apache пользьзователем).
 *
 * @category Mirasvit
 */
class Mirasvit_FpcCrawler_Fpccrawlerlogged_ActionController extends Mage_Core_Controller_Front_Action
{
    public function runAction()
    {
        Mage::getSingleton('fpccrawler/crawlerlogged_crawl')->doRun();
    }
}
