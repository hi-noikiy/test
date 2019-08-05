<?php
/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplusprogram\Observer\AbtractObserver;

class GetListProgramWelcome extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $programListObj = $observer->getEvent()->getProgramListObject();

        $programList = $programListObj->getProgramList();
        if (isset($programList['default'])) {
            if (!$this->_helper->showDefault()) {
                unset($programList['default']);
            }
        }
        $collection = $this->_programCollectionFactory->create()
            ->setStoreId($this->getStore()->getId());
        foreach ($collection as $item){
            if ($item->getStatus()==1 && $item->getShowInWelcome()) {
                $this->_eventManager->dispatch('affiliateplus_prepare_program', ['info' => $item]);
                $programList[$item->getId()] = $item;
            }
        }
        $programListObj->setProgramList($programList);
        return $this;
    }
}