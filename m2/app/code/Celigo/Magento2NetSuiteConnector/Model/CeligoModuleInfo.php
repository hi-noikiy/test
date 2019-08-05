<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model;

use \Celigo\Magento2NetSuiteConnector\Logger\Logger;
use \Magento\Framework\Module\ResourceInterface;

/**
 * Celigo sales order repository object.
 */
class CeligoModuleInfo implements \Celigo\Magento2NetSuiteConnector\Api\CeligoModuleInfoInterface
{

    private $logger;

    private $moduleResource;
   
    public function __construct(
        Logger $logger,
        ResourceInterface $moduleResource
    ) {
        $this->logger = $logger;
        $this->moduleResource = $moduleResource;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->moduleResource->getDbVersion('Celigo_Magento2NetSuiteConnector');
    }
}
