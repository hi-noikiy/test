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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Api\Service\Rma\RmaManagement;


use Mirasvit\Rma\Api\Data\RmaInterface;

interface SaveInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     * @param array $data
     * @param array $items
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function saveRma($performer, $data, $items);

    /**
     * @param RmaInterface $rma
     * @return void
     */
    public function markAsRead(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return void
     */
    public function markAsUnread(RmaInterface $rma);
}