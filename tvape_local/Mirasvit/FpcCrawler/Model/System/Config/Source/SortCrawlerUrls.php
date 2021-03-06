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
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Model_System_Config_Source_SortCrawlerUrls
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'popularity', 'label' => __('Popularity')),
            array('value' => 'custom_order', 'label' => __('Custom order')),
        );
    }
}
