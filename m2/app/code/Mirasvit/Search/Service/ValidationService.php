<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.75
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Service;

use Mirasvit\Core\Service\AbstractValidator;
use Magento\Framework\Module\Manager;

class ValidationService extends AbstractValidator
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function testMageworksSearchSuiteConflict()
    {
        if ($this->moduleManager->isEnabled('Mageworks_SearchSuite')) {
            return [self::FAILED, __FUNCTION__, 'Please disable or delete Mageworks_SearchSuite module'];
        } else {
            return [self::SUCCESS, __FUNCTION__, []];
        }
    }
}