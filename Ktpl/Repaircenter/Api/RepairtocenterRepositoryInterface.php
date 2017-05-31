<?php
namespace Ktpl\Repaircenter\Api;

use Ktpl\Repaircenter\Model\Repairtocenter;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RepairtocenterRepositoryInterface 
{
    public function save(Repairtocenter $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(Repairtocenter $page);

    public function deleteById($id);
}
