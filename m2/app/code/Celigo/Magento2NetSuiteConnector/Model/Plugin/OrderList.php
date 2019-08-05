<?php
namespace Celigo\Magento2NetSuiteConnector\Model\Plugin;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\CollectionFactory;

class OrderList
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
 
    /**
     * List of watched extension attributes.
     *
     * @var array
     */
    private $watchedExtensionAttributes = ['is_exported_to_io'];

    /**
     * @var celigoSalesOrderCollection
     */
    private $celigoSalesOrderCollection;
 
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionFactory $celigoSalesOrderCollection
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $celigoSalesOrderCollection
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->celigoSalesOrderCollection = $celigoSalesOrderCollection;
    }
 
    /**
     * Detects usage of necessary extension attributes in search criteria.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $object
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     */
    public function beforeGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $object,
        \Magento\Framework\Api\SearchCriteria $searchCriteria
    ) {
        $filters = [];
        $defaultFilters = [];
        $defaultPageSize = $searchCriteria->getPageSize();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (in_array($filter->getField(), $this->watchedExtensionAttributes)) {
                    $filters[] = [
                        'field' => $filter->getField(),
                        'value' => $filter->getValue(),
                        'type' => $filter->getConditionType(),
                    ];
                } else {
                    $defaultFilters[] = [
                        'field' => $filter->getField(),
                        'value' => $filter->getValue(),
                        'type' => $filter->getConditionType(),
                    ];
                }
            }
        }
        $searchCriteria = empty($filters) ? $searchCriteria : $this->createSearchCriteria(
            $filters,
            $defaultFilters,
            $defaultPageSize
        );
        return ['searchCriteria' => $searchCriteria];
    }
 
    /**
     * Creates search criteria based on orderIds.
     *
     * @param array $filters
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function createSearchCriteria(array $filters, $defaultFilters = [], $defaultPageSize = 5)
    {
        $orderIds = [];
        $celigoSalesOrderCollection = $this->celigoSalesOrderCollection->createSalesOrderCollection($defaultFilters);
        $celigoSalesOrderCollection->addFieldToSelect('parent_id');
        foreach ($filters as $filter) {
            $collection = clone $celigoSalesOrderCollection;
            $collection->addFieldToFilter("main_table.{$filter['field']}", ['eq' => $filter['value']]);
            $collection->setPageSize($defaultPageSize);
            $orderIds = $collection->getColumnValues('parent_id');
            $this->searchCriteriaBuilder->addFilter('entity_id', array_values($orderIds), 'in');
        }

        return $this->searchCriteriaBuilder->create();
    }
}
