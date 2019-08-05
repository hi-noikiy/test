<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Ui\Component\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

/**
 * Class FilterPool
 *
 * @api
 */
class FilterPool extends \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool
{
    /**
     * @param Collection $collection
     * @param SearchCriteriaInterface $criteria
     * @return void
     */
    public function applyFilters(Collection $collection, SearchCriteriaInterface $criteria)
    {
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {

                if ($filter->getField() == 'created_at') {
                    $filter->setField('main_table.created_at');
                }
                if ($filter->getField() == 'status') {
                    $filter->setField('main_table.status');
                }
                if ($filter->getField() == 'increment_id') {
                    $filter->setField('sales_order.increment_id');
                }

                /** @var $filterApplier FilterApplierInterface*/
                if (isset($this->appliers[$filter->getConditionType()])) {
                    $filterApplier = $this->appliers[$filter->getConditionType()];
                } else {
                    $filterApplier = $this->appliers['regular'];
                }
                $filterApplier->apply($collection, $filter);
            }
        }
    }
}
