<?php
/**
 * Copyright © 2017 CyberSource. All rights reserved.
 * See accompanying License.txt for applicable terms of use and license.
 */
namespace CollinsHarper\VisaCheckout\Block\Test\Unit\Gateway\Response;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class AddressTest
 * @package CyberSource\Address\Test\Unit\Controller\Index
 * @codingStandardsIgnoreStart
 */
class AuthorizeResponseHandlerTest extends \PHPUnit_Framework_TestCase
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
        $this->paymentMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestDataBuilderMock = $this
            ->getMockBuilder(\CollinsHarper\VisaCheckout\Helper\RequestDataBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this
            ->getMockBuilder(\CollinsHarper\VisaCheckout\Gateway\Helper\SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['reserveOrderId', 'getReservedOrderId', 'getGrandTotal'])
            ->getMock();
        $helper = new ObjectManager($this);
        $this->unit = $helper->getObject(
            \CollinsHarper\VisaCheckout\Gateway\Response\AuthorizeResponseHandler::class,
            [
                'context' => $this->contextMock,
                'requestDataBuilder' => $this->requestDataBuilderMock,
                'subjectReader' => $this->subjectReaderMock,
                'session' => $this->sessionMock,
            ]
        );
    }
    
    public function testHandle()
    {
        $response = new \stdClass();
        $response->responseData = [
            'ReferenceNum' => 1,
            'ReceiptId' => 1,
            'TransAmount' => 1,
            'ResponseCode' => 1,
            'Message' => 1,
            'TransID' => 1,
        ];
        $this->subjectReaderMock
            ->method('readPayment')
            ->will($this->returnValue($this->paymentDataMock));
        $this->paymentDataMock
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
        $this->sessionMock
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->quoteMock
            ->method('reserveOrderId')
            ->will($this->returnValue($this->quoteMock));
        $this->assertEquals(null, $this->unit->handle(['payment' => $this->paymentDataMock], ['response' => $response]));
    }
}