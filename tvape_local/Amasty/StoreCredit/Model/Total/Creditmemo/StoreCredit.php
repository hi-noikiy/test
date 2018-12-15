<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Total_Creditmemo_StoreCredit extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{


    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

        $order = $creditmemo->getOrder();
        if ($order->getBaseAmstcredAmount() && $order->getBaseAmstcredAmountInvoiced() != 0) {
            $baseAmountLeft = $order->getBaseAmstcredAmountInvoiced() - $order->getBaseAmstcredAmountRefunded();

            if ($baseAmountLeft >= $creditmemo->getBaseGrandTotal()) {
                $baseUsed = $creditmemo->getBaseGrandTotal();
                $used = $creditmemo->getGrandTotal();

                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setGrandTotal(0);

                $creditmemo->setAllowZeroGrandTotal(true);
            } else {
                $baseUsed = $order->getBaseAmstcredAmountInvoiced() - $order->getBaseAmstcredAmountRefunded();
                $used = $order->getAmstcredAmountInvoiced() - $order->getAmstcredAmountRefunded();

                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseUsed);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $used);
            }

            $creditmemo->setBaseAmstcredAmount($baseUsed);
            $creditmemo->setAmstcredAmount($used);
        }
        return $this;
    }
}
