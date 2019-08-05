<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model\Source;

use Aheadworks\StoreCredit\Model\Source\BalanceUpdateActions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\StoreCredit\Model\Source\TransactionType;

/**
 * Test for \Aheadworks\StoreCredit\Model\Source\BalanceUpdateActions
 */
class BalanceUpdateActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BalanceUpdateActions
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TransactionType
     */
    private $transactionTypeMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->transactionTypeMock = $this->getMockBuilder(TransactionType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBalanceUpdateActions'])
            ->getMock();
        $this->model = $objectManager->getObject(
            BalanceUpdateActions::class,
            [
                'transactionType' => $this->transactionTypeMock
            ]
        );
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->transactionTypeMock->expects($this->once())
            ->method('getBalanceUpdateActions')
            ->willReturn([]);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
