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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



abstract class Mirasvit_Rma_Helper_Rma_Save_AbstractPostDataProcessor extends Mage_Core_Helper_Abstract
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}