<?php
/**
 * Copyright © 2017 CyberSource. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */
namespace CollinsHarper\VisaCheckout\Block\Test\Unit\Gateway\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class AddressTest
 * @package CyberSource\Address\Test\Unit\Controller\Index
 * @codingStandardsIgnoreStart
 */
class CanVoidHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    
    /**
     *
     * @var CollinsHarper\Masterpass\Block\Form
     */
    private $unit;
    
    protected function setUp()
    {
        $this->contextMock = $this
            ->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataMock = $this
            ->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helper = new ObjectManager($this);
        $this->unit = $helper->getObject(
            \CollinsHarper\VisaCheckout\Gateway\Config\CanVoidHandler::class,
            [
                'context' => $this->contextMock,
            ]
        );
    }
    
    public function testExecute()
    {
        $this->assertEquals(null, $this->unit->handle(['payment' => $this->paymentDataMock], 1));
    }
}