<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Adminhtml_Amoptimization_ProcessController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Minify js files
     */
    public function runAction()
    {
        try {
            Mage::getModel('amoptimization/observer_cron')->processQueue();
            $fails = $this->_getSession()->getMinificationFails();
            if ($fails) {
                $this->_getSession()->addError(
                    $this->__('%s files not processed', $fails)
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__('Minification javascript success.')
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                $this->__('An error occurred while javascript optimize.')
            );
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'amoptimization'));
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/amoptimization');
    }
}
