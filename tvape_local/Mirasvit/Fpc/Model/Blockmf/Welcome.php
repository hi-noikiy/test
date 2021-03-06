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



class Mirasvit_Fpc_Model_Blockmf_Welcome extends Mirasvit_Fpc_Model_Blockmf_Abstract
{
    /**
     * @return string
     */
    protected function _renderBlock()
    {
        $welcome = Mage::app()->getLayout()->createBlock('page/html_header')->getWelcome();

        return $welcome;
    }
}
