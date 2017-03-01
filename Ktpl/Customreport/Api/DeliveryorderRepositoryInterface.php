<?php
namespace Ktpl\Customreport\Api;

use Ktpl\Customreport\Model\DeliveryorderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface DeliveryorderRepositoryInterface 
{
    public function save(DeliveryorderInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(DeliveryorderInterface $page);

    public function deleteById($id);
}
