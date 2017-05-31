<?php
namespace Ktpl\Customreport\Api;

use Ktpl\Customreport\Model\WholesalerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface WholesalerRepositoryInterface 
{
    public function save(WholesalerInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(WholesalerInterface $page);

    public function deleteById($id);
}
