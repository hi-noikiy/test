<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysafe\Paysafe\Model\Method;

class Creditcard extends \Paysafe\Paysafe\Model\Method\AbstractMethod
{
    protected $_code = 'paysafe_creditcard';
    protected $_methodTitle = 'Paysafe Credit Card';

    /**
     * get a brand
     * @return string
     */
    public function getBrand()
    {
        $cardSelection = $this->getSpecificConfiguration('card_selection');
        if (isset($cardSelection)) {
            return $cardSelection;
        }
        return $this->brand;
    }
}
