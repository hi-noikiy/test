<?php
namespace Ktpl\Customreport\Api;

use Ktpl\Customreport\Model\CimorderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface CimorderRepositoryInterface 
{
    public function save(CimorderInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(CimorderInterface $page);

    public function deleteById($id);
}
