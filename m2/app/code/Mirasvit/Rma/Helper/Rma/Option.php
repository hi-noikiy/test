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



namespace Mirasvit\Rma\Helper\Rma;

class Option extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->statusRepository      = $statusRepository;
        $this->context               = $context;

        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\StatusInterface[]
     */
    public function getStatusList()
    {
        $sortOrderSort = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection( \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addSortOrder($sortOrderSort)
        ;

        return $this->statusRepository->getList($searchCriteria->create())->getItems();
    }
}