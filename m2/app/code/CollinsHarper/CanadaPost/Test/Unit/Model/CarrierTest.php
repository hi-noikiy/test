<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Unit\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Xml\Security;

class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpResponse;

    /**
     * @var \CollinsHarper\CanadaPost\Model\Carrier
     */
    protected $_model;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $error;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorFactory;

    /**
     * @var \CollinsHarper\CanadaPost\Model\Carrier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scope;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scope = $this->getMockBuilder(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();

        $this->scope->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'scopeConfiggetValue'])
        );

        // xml element factory
        $xmlElFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Simplexml\ElementFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $xmlElFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

                    return $helper->getObject(
                        '\Magento\Shipping\Model\Simplexml\Element',
                        ['data' => $data['data']]
                    );
                }
            )
        );

        // rate factory
        $rateFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Rate\ResultFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateResult = $this->getMockBuilder(
            '\Magento\Shipping\Model\Rate\Result'
        )->disableOriginalConstructor()->setMethods(
            null
        )->getMock();
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rateResult));

        // rate method factory
        $rateMethodFactory = $this->getMockBuilder(
            '\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            'Magento\Quote\Model\Quote\Address\RateResult\Method'
        )->disableOriginalConstructor()->setMethods(
            ['setPrice']
        )->getMock();
        $rateMethod->expects($this->any())->method('setPrice')->will($this->returnSelf());

        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));

        // http client
        $this->_httpResponse = $this->getMockBuilder(
            '\Zend_Http_Response'
        )->disableOriginalConstructor()->setMethods(
            ['getBody']
        )->getMock();

        $httpClient = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClient'
        )->disableOriginalConstructor()->setMethods(
            ['request']
        )->getMock();
        $httpClient->expects($this->any())->method('request')->will($this->returnValue($this->_httpResponse));

        $httpClientFactory = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClientFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $httpClientFactory->expects($this->any())->method('create')->will($this->returnValue($httpClient));
        $modulesDirectory = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Read'
        )->disableOriginalConstructor()->setMethods(
            ['getRelativePath', 'readFile']
        )->getMock();
        $modulesDirectory->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/../_files/countries.xml'))
        );
        $readFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $readFactory->expects($this->any())->method('create')->willReturn($modulesDirectory);
        $storeManager = $this->getMockBuilder(
            '\Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->setMethods(
            ['getWebsite']
        )->getMock();
        $website = $this->getMockBuilder(
            '\Magento\Store\Model\Website'
        )->disableOriginalConstructor()->setMethods(
            ['getBaseCurrencyCode', '__wakeup']
        )->getMock();
        $website->expects($this->any())->method('getBaseCurrencyCode')->will($this->returnValue('CAD'));
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));

        $this->error = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\RateResult\Error')
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder('Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $this->_model = $this->_helper->getObject(
            'CollinsHarper\CanadaPost\Model\Carrier',
            [
                'scopeConfig' => $this->scope,
                'xmlSecurity' => new Security(),
                'xmlElFactory' => $xmlElFactory,
                'rateFactory' => $rateFactory,
                'rateErrorFactory' => $this->errorFactory,
                'rateMethodFactory' => $rateMethodFactory,
                'httpClientFactory' => $httpClientFactory,
                'readFactory' => $readFactory,
                'storeManager' => $storeManager,
                'data' => ['id' => 'cpcanadapost', 'store' => '1']
            ]
        );
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        $pathMap = [
            'carriers/cpcanadapost/is_mock' => true,

        ];
        return isset($pathMap[$path]) ? $pathMap[$path] : null;
    }


    public function testCollectRatesFail()
    {

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertFalse(false, $this->_model->collectRates($request));
    }
    public function testReturnLanguage()
    {

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertTrue(is_bool($this->_model->returnFrench()));
    }
}