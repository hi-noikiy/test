<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Logger;

use Magento\Framework\Filesystem\DriverInterface;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    public function __construct(
        DriverInterface $filesystem,
        \Magento\Framework\Filesystem $corefilesystem
    ) {
        
        $corefilesystem= $corefilesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $logpath = $corefilesystem->getAbsolutePath('log/');

        $filename = 'celigo.log';
        $filepath = $logpath . $filename;
        parent::__construct(
            $filesystem,
            $filepath
        );
    }
}
