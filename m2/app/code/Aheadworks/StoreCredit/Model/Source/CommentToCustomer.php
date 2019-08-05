<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Source;

use Aheadworks\StoreCredit\Model\Comment\CommentPool;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Aheadworks\StoreCredit\Model\Source\CommentToCustomer
 */
class CommentToCustomer implements ArrayInterface
{
    /**
     * @var CommentPool
     */
    private $commentPool;

    /**
     * @var array
     */
    private $comments;

    /**
     * @param CommentPool $commentPool
     */
    public function __construct(CommentPool $commentPool)
    {
        $this->commentPool = $commentPool;
    }

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        if ($this->comments == null) {
            $this->comments = [];
            foreach ($this->commentPool->getAllComments() as $commentInstance) {
                $this->comments[] = [
                    'value' => $commentInstance->getComment(),
                    'label' => $commentInstance->getLabel()
                ];
            }
        }
        return $this->comments;
    }
}
