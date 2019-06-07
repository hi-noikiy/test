<?php
/**
 * MageWorx
 * MageWorx SeoCrossLinks Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoCrossLinks
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoCrossLinks_Helper_Action extends Mage_Core_Helper_Abstract
{
    /**
     * Retrive current fullaction name
     *
     * @return string
     */
    public function getCurrentFullActionName()
    {
        $controller = Mage::app()->getFrontController();
        if (is_object($controller) && is_callable(array($controller, 'getAction'))) {
            $action = $controller->getAction();
            if (is_object($action) && is_callable(array($action, 'getFullActionName'))) {
                $actionName = $action->getFullActionName();
                if ($actionName) {
                    return $actionName;
                }
            }
        }
        return null;
    }
}