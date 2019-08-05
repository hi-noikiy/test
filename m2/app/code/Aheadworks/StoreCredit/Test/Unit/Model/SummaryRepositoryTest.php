<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Test\Unit\Model;

use Aheadworks\StoreCredit\Model\SummaryRepository;
use Aheadworks\StoreCredit\Model\ResourceModel\Summary as SummaryResource;
use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Api\Data\SummaryInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Aheadworks\StoreCredit\Test\Unit\Model\SummaryRepositoryTest
 */
class SummaryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SummaryRepository
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SummaryResource
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SummaryInterfaceFactory
     */
    private $modelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SummaryInterface
     */
    private $dataModelMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->getMockBuilder(SummaryResource::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'loadByCustomerId',
                    'save',
                    'getIdByCustomerId',
                    'beginTransaction',
                    'commit',
                    'rollBack',
                ]
            )
            ->getMock();

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['load', 'save', 'delete']
            )
            ->getMock();

        $this->modelFactoryMock = $this->getMockBuilder(SummaryInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->dataModelMock = $this->getMockBuilder(SummaryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getCustomerId', 'getSummaryId']
            )
            ->getMockForAbstractClass();

        $data = [
            'resource' => $this->resourceMock,
            'entityManager' => $this->entityManagerMock,
            'summaryFactory' => $this->modelFactoryMock,
        ];

        $this->object = $objectManager->getObject(SummaryRepository::class, $data);
    }

    /**
     * Test get method
     */
    public function testGetMethod()
    {
        $customerId = 10;
        $summaryId = 1;

        $this->resourceMock->expects($this->once())
            ->method('getIdByCustomerId')
            ->with($customerId)
            ->willReturn($summaryId);

        $this->modelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->dataModelMock, $summaryId)
            ->willReturnSelf();

        $this->dataModelMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->dataModelMock->expects($this->exactly(2))
            ->method('getSummaryId')
            ->willReturn($summaryId);

        $actual = $this->object->get($customerId);

        $this->assertSame($actual, $this->object->get($customerId));
    }

    /**
     * Test getById method
     */
    public function testGetByIdMethod()
    {
        $customerId = 10;
        $summaryId = 1;

        $this->modelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->dataModelMock, $summaryId)
            ->willReturnSelf();

        $this->dataModelMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->dataModelMock->expects($this->exactly(2))
            ->method('getSummaryId')
            ->willReturn($summaryId);

        $expected = $this->object->getById($summaryId);

        $this->assertSame($expected, $this->dataModelMock);
        $this->assertSame($expected, $this->object->getById($summaryId));
    }

    /**
     * Test save method
     */
    public function testSaveMethod()
    {
        $this->dataModelMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(11);

        $this->dataModelMock->expects($this->once())
            ->method('getSummaryId')
            ->willReturn(1);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->dataModelMock)
            ->willReturnSelf();

        $actual = $this->object->save($this->dataModelMock);

        $this->assertEquals($this->dataModelMock, $actual);
    }

    /**
     * Test delete method
     */
    public function testDeleteMethod()
    {
        $this->dataModelMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(12);

        $this->dataModelMock->expects($this->once())
            ->method('getSummaryId')
            ->willReturn(1);

        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->dataModelMock)
            ->willReturnSelf();

        $this->assertTrue($this->object->delete($this->dataModelMock));
    }

    /**
     * Test deleteById method
     */
    public function testDeleteByIdMethod()
    {
        $customerId = 10;
        $summaryId = 1;

        $this->modelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->dataModelMock, $summaryId)
            ->willReturnSelf();

        $this->dataModelMock->expects($this->exactly(2))
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->dataModelMock->expects($this->exactly(3))
            ->method('getSummaryId')
            ->willReturn($summaryId);

        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->dataModelMock)
            ->willReturnSelf();

        $this->assertTrue($this->object->deleteById($summaryId));
    }
}
