<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\Storelocator\Setup\Operation\CreateReviewTable;

class Review extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateReviewTable::TABLE_NAME, 'id');
    }
}
