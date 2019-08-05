<?php
namespace Ktpl\Ordercolumn\Model\Plugin\Sales\Order;

class Grid
{

    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'sales_order';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.entity_id = main_table.entity_id",
                    [
                        'samples' => 'co.samples',
                        'business_developement' => 'co.business_developement',
                        'tax_code' => 'co.tax_code',
                        'order_type' => 'co.order_type'
                    ]
                );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);

            //echo $collection->getSelect()->__toString();die;

        }
        return $collection;

    }

}