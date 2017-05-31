<?php
namespace Ktpl\Repaircenter\Api;

use Ktpl\Repaircenter\Model\Repairtocustomer;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RepairtocustomerRepositoryInterface 
{
    public function save(Repairtocustomer $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(Repairtocustomer $page);

    public function deleteById($id);
}
