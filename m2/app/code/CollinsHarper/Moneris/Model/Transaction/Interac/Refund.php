<?php
/**
 * Copyright © 2016 CollinsHarper. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CollinsHarper\Moneris\Model\Transaction\Interac;

use CollinsHarper\Moneris\Model\Transaction\Refund as TransactionRefund;

/**
 * Moneres Interac Online Payment Method model.
 */
class Refund extends TransactionRefund
{
    protected $_requestType = "idebit_refund";

    public function buildTransactionArray()
    {
        $payment = $this->getPayment();

        if (!$payment) {
            return [];
        }

        $this->_requestType = $this->getUpdatedRequestType();

        return [
            'type'          => $this->_requestType,
            'order_id'      => $payment->getLastTransId(),
            'amount'        => $this->getAmount(),
            'txn_number'    => $payment->getCcTransId()
        ];
    }
}
