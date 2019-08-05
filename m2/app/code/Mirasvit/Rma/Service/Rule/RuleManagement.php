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


namespace Mirasvit\Rma\Service\Rule;

/**
 *  We put here only methods directly connected with Rule properties
 */
class RuleManagement implements \Mirasvit\Rma\Api\Service\Rule\RuleManagementInterface
{
    public function __construct(
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->ruleRepository        = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return \Magento\Framework\Api\AbstractSimpleObject
     */
    protected function getSortOrder()
    {
        return $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection(\Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventRules($eventType)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('event', $eventType)
            ->addSortOrder($this->getSortOrder())
        ;

        return $this->ruleRepository->getList($searchCriteria->create())->getItems();
    }
}

