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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoSaveAfter implements ObserverInterface
{
    public function __construct(
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->backendSession = $backendSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getDataObject();
        $session = $this->backendSession;
        if ($rmaId = $session->getRmaId()) {
            $rma = $this->rmaFactory->create()->load($rmaId);
            $ids = $rma->getCreditMemoIds();
            $ids[] = $creditmemo->getId();
            $rma->setCreditMemoIds($ids);
            $rma->save();
            $session->unsetRmaId();
        }
    }
}
