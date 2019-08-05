<?php

namespace CollinsHarper\Moneris\Logger;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

class Handler extends Base
{
    protected $fileName = '/var/log/ch_moneris.log';
    protected $loggerType = Logger::DEBUG;
}