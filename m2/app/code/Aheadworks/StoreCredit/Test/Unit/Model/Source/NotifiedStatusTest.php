<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model\Source;

use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\StoreCredit\Model\Source\NotifiedStatus
 */
class NotifiedStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NotifiedStatus
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            NotifiedStatus::class,
            []
        );
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
