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


namespace Mirasvit\Rma\Service\Strategy;

class Search implements \Mirasvit\Rma\Api\Service\Strategy\SearchInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->rmaRepository         = $rmaRepository;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList($customerId, \Magento\Sales\Model\Order $order = null)
    {
        if (!$customerId && ! $order) {
            return [];
        }

        $sortOrderSort = $this->sortOrderBuilder
            ->setField('rma_id')
            ->setDirection( \Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addSortOrder($sortOrderSort);
        if ($customerId) {
            $searchCriteria->addFilter('main_table.customer_id', $customerId);
        }

        if ($order) {
            $searchCriteria->addFilter('main_table.order_id', $order->getId());
        }

        return $this->rmaRepository->getList($searchCriteria->create())->getItems();
    }
}

