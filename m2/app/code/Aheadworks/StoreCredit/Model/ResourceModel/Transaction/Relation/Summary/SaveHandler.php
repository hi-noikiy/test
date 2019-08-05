<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\ResourceModel\Transaction\Relation\Summary;

use Aheadworks\StoreCredit\Model\Service\SummaryService;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class Aheadworks\StoreCredit\Model\ResourceModel\Transaction\Relation\Summary\SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var SummaryService
     */
    private $summaryService;

    /**
     * @param SummaryService $summaryService
     */
    public function __construct(
        SummaryService $summaryService
    ) {
        $this->summaryService = $summaryService;
    }

    /**
     *  {@inheritDoc}
     */
    public function execute($entity, $arguments = [])
    {
        $this->summaryService->addSummaryToCustomer($entity);
        return $entity;
    }
}
