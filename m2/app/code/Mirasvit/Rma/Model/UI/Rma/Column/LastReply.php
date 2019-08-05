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


namespace Mirasvit\Rma\Model\UI\Rma\Column;

use Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface as RmaSearchManagement;
use Mirasvit\Rma\Model\RmaFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class LastReply extends Column
{
    public function __construct(
        RmaFactory $rmaFactory,
        RmaSearchManagement $rmaSearchManagement,
        UserFactory $userFactory,
        Repository $assetRepo,
        Escaper $escaper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->rmaSearchManagement = $rmaSearchManagement;
        $this->userFactory = $userFactory;
        $this->assetRepo = $assetRepo;
        $this->escaper = $escaper;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if ($item[$name]) {
                    $rma = $this->rmaFactory->create()->setId($item['rma_id']);
                    // If last message is automated, assign Last Reply Name value to owner, if such exists
                    $lastMessage = $this->rmaSearchManagement->getLastMessage($rma);
                    if ($lastMessage && !$lastMessage->getCustomerId()) {
                        if (!$lastMessage->getUserId()) {
                            $item[$name] = '';
                        } else {
                            $user = $this->userFactory->create();
                            $user->getResource()->load($user, $lastMessage->getUserId());
                            $item[$name] = $user->getName();
                        }
                    }
                    $item[$name] = $this->escaper->escapeHtml($item[$name]);
                    if (!$rma->getIsAdminRead()) {
                        $item[$name] .= ' <img style="margin-right: 20px;" src="' .
                            $this->assetRepo->getUrl('Mirasvit_Rma::images/fam_newspaper.gif') . '">';
                    }
                } else {
                    $item[$name] = '';
                }

            }
        }

        return $dataSource;
    }
}