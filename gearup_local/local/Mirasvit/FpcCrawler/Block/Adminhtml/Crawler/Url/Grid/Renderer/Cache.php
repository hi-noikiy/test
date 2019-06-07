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
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Block_Adminhtml_Crawler_Url_Grid_Renderer_Cache
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $row->isCacheExist() ?
            '<span class="grid-severity-notice"><span>In Cache</span></span>'
            : '<span class="grid-severity-major"><span>Pending</span></span>';
    }
}
