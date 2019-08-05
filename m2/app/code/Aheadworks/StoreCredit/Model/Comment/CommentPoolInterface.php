<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Comment;

/**
 * Interface Aheadworks\StoreCredit\Model\Comment\CommentPoolInterface
 */
interface CommentPoolInterface
{
    /**
     * Retrieve comment model
     *
     * @param string $comment
     * @return CommentInterface
     */
    public function get($comment);
}
