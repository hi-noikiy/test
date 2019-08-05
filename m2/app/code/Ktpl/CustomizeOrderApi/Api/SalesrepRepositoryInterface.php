<?php

namespace Ktpl\CustomizeOrderApi\Api;

/**
 * Salesrep interface.
 *
 * @api
 */
interface SalesrepRepositoryInterface
{
    /**
     * Loads a specified salesrep.
     *
     * @param int $order_id The order ID.
     * @return \Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface Salesrep interface.
     */
    public function getByOrderId($order_id);

    /**
     * Performs persist operations for a specified salesrep.
     *
     * @param \Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface $salesrepInterface.
     * @return \Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface Salesrep interface.
     */
    public function save(\Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface $salesrepInterface);

    /**
     * Performs persist operations for a specified salesrep.
     *
     * @param int $order_id.
     * @param int $rep_commission.
     * @return int||null
     */
    public function getRepCommissionEarned($order_id, $rep_commission);

    /**
     * Performs persist operations for a specified salesrep.
     *
     * @param int $order_id.
     * @param int $manager_commission_rate.
     * @param int $salesrep_commission.
     * @return int||null
     */
    public function getManagerCommissionEarned(
        $order_id,
        $manager_commission_rate,
        $salesrep_commission
    );
    /**
     * Performs persist operations for a specified salesrep.
     *
     * @return \Ktpl\CustomizeOrderApi\Api\Data\SalesrepInterface Salesrep interface
     */
    public function get();
}
