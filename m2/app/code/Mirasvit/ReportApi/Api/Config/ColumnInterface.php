<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.3.16
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Api\Config;

interface ColumnInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return TableInterface
     */
    public function getTable();

    /**
     * @return TypeInterface
     */
    public function getType();

    /**
     * @return AggregatorInterface
     */
    public function getAggregator();

    /**
     * @return bool
     */
    public function isUnique();

    /**
     * @return bool
     */
    public function isInternal();

    /**
     * @return \Zend_Db_Expr
     */
    public function toDbExpr();

    /**
     * @return FieldInterface[]
     */
    public function getFields();

    /**
     * @param SelectInterface $select
     * @return bool
     */
    public function join(SelectInterface $select);

    /**
     * @param SelectInterface $select
     * @return bool
     */
    public function joinRight(SelectInterface $select);
}