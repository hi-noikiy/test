<?php

namespace Ktpl\Wholesaler\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Tierdiscount extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setTierDiscount(0);
        $creditmemo->setBaseTierDiscount(0);

        $amount = $creditmemo->getOrder()->getTierDiscount();
        $creditmemo->setTierDiscount($amount);

        $amount = $creditmemo->getOrder()->getBaseTierDiscount();
        $creditmemo->setBaseTierDiscount($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getTierDiscount());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseTierDiscount());

        return $this;
    }
}
