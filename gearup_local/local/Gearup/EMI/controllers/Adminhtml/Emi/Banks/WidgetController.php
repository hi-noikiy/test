<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Bank admin widget controller
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Adminhtml_Emi_Banks_WidgetController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Chooser Source action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function chooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $grid = $this->getLayout()->createBlock(
            'gearup_emi/adminhtml_banks_widget_chooser',
            '',
            array(
                'id' => $uniqId,
            )
        );
        $this->getResponse()->setBody($grid->toHtml());
    }
}
