<?php

/*
 * @author     Kristof Ringleff
 * @package    Fooman_Connect
 * @copyright  Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_Connect_Model_System_ShippingAccountOptions extends Fooman_Connect_Model_System_AbstractAccounts
{

    public function toOptionArray()
    {
        $returnArray = array();
        try {
            $accounts = $this->getXeroAccounts();
            $wantedAccountTypes = array('REVENUE', 'SALES', 'DIRECTCOSTS', 'EXPENSE', 'OTHERINCOME');

            foreach ($accounts as $account) {
                if (in_array($account['Type'], $wantedAccountTypes)) {
                    $returnArray[] = array(
                        'value' => $account['Code'],
                        'label' => '[' . $account['Code'] . "] " . substr($account['Name'], 0, 30)
                    );
                }
            }
        } catch (Exception $e) {
            $returnArray[] = array('value' => '0', 'label' => $e->getMessage());
        }
        return $returnArray;
    }
}
