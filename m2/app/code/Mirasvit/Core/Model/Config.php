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
 * @package   mirasvit/module-core
 * @version   1.2.62
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Core\Model;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Template\FilterProvider as CmsFilterProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isIncludeFontAwesome()
    {
        return (bool)$this->scopeConfig->getValue('mst_core/css/include_font_awesome');
    }


    /**
     * @return string
     */
    public function getFeedbackUrl()
    {
        return 'https://mirasvit.com/feedback/share';
    }

    /**
     * @return bool
     */
    public function isFeedbackActive()
    {
        return (bool)$this->scopeConfig->getValue('mst_core/feedback/is_active');
    }
}