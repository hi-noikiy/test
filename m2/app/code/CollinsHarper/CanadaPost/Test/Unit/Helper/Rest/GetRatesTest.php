<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Test\Unit\Helper\Rest;


use Magento\Framework\Xml\Security;

class GetRatesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Ups config helper
     *
     * @var \Magento\Ups\Helper\Config
     */
    protected $helper;


    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject('CollinsHarper\CanadaPost\Helper\Rest\GetRates');
        $this->helper->setDataFactory($objectManagerHelper->getObject('CollinsHarper\CanadaPost\Helper\DataFactory'));
        $this->helper->setChLogger($objectManagerHelper->getObject('CollinsHarper\Core\Logger\Logger'));
        $this->helper->setOptionHelper($objectManagerHelper->getObject('CollinsHarper\CanadaPost\Helper\Option'));
        $this->helper->setMockData( require __DIR__ . '/../../_files/rates_request_canadapost_data.php');
    }



    public function testRateRequest()
    {
        $requestData = json_decode($this->helper->getConfigValue('rate_request_json'), true);
        $rates = $this->helper->getRates($requestData);

        $this->assertTrue(count($rates) > 0);
        $item = array_pop($rates);
        $this->assertTrue(isset($item['code']));
        $this->assertTrue(isset($item['price']));

    }
}
