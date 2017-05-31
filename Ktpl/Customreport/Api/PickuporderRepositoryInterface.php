<?php
namespace Ktpl\Customreport\Api;

use Ktpl\Customreport\Model\PickuporderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PickuporderRepositoryInterface 
{
    public function save(PickuporderInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(PickuporderInterface $page);

    public function deleteById($id);
}
