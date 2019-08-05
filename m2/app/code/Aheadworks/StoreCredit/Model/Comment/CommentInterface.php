<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Comment;

/**
 * Interface Aheadworks\StoreCredit\Model\Comment\CommentInterface
 */
interface CommentInterface
{
    /**
     * Retrieve comment type
     *
     * @return int
     */
    public function getType();

    /**
     * Retrieve comment label
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    public function getLabel($key = null, $arguments = []);

    /**
     * Render comment key to comment label
     *
     * @param array $arguments
     * @param string $key
     * @param string $label
     * @param bool $renderingUrl
     * @param bool $frontend
     * @return array
     */
    public function renderComment(
        $arguments = [],
        $key = null,
        $label = null,
        $renderingUrl = false,
        $frontend = false
    );
}
