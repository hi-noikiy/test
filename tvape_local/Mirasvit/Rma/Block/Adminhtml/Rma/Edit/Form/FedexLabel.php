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



class Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form_FedexLabel extends Mirasvit_Rma_Block_Adminhtml_Rma_Edit_Form
{
    /*
     *  Constructs block with FedEx label .
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mst_rma/rma/edit/form/fedex_label.phtml');
    }

    /**
     * @return Mirasvit_Rma_Model_Fedex_Label[]
     */
    public function getLabels()
    {
        $labels = Mage::getModel('rma/fedex_label')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRma()->getId());
        return $labels;
    }
}
