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
 * @package   mirasvit/module-helpdesk
 * @version   1.1.59
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Service\Ticket;

class TicketManagement implements \Mirasvit\Helpdesk\Api\Service\Ticket\TicketManagementInterface
{
    public function __construct(
        \Mirasvit\Helpdesk\Model\Config $config,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config                = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function isRmaExists($ticket)
    {
        return (bool)count($this->getRmas($ticket));
    }

    /**
     * {@inheritdoc}
     */
    public function getRmas($ticket)
    {
        if (!$this->config->isActiveRma()) {
            return [];
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $rmaRepository = $objectManager->create('\Mirasvit\Rma\Api\Repository\RmaRepositoryInterface');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('ticket_id', $ticket->getId())
        ;

        return $rmaRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRmasOptions($ticket)
    {
        $options = [];
        if (!$this->config->isActiveRma()) {
            return $options;
        }
        $rmas = $this->getRmas($ticket);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Mirasvit\Rma\Helper\Rma\Url $rmaUrlHelpder */
        $rmaUrlHelpder = $objectManager->create('\Mirasvit\Rma\Helper\Rma\Url');

        foreach ($rmas as $rma) {
            $options[] = [
                'name' => $rma->getIncrementId(),
                'id'   => $rma->getId(),
                'url'  => $rmaUrlHelpder->getBackendUrl($rma),
            ];
        }

        return $options;
    }
}

