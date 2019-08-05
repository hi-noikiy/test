<?php
namespace Cminds\Salesrep\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;

class SaveSalesrepCheckout implements ObserverInterface
{
    protected $checkoutSession;

    protected $salesrepRepositoryInterface;

    protected $adminUsers;

    protected $scopeConfig;

    protected $salesrepHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cminds\Salesrep\Api\SalesrepRepositoryInterface $salesrepRepositoryInterface,
        \Magento\User\Model\ResourceModel\User\Collection $adminUsers,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cminds\Salesrep\Helper\Data $salesrepHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->salesrepRepositoryInterface = $salesrepRepositoryInterface;
        $this->adminUsers = $adminUsers;
        $this->scopeConfig = $scopeConfig;
        $this->salesrepHelper = $salesrepHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        $quote = $observer->getQuote();

        $defaultStatus = $this->salesrepHelper->getDefaultCommissionStatus();

        if ($order->getId()) {
            $salesrepId = '';
            $selectedSalesrepId = $this->checkoutSession->getSelectedSalesrepId();

            if ($selectedSalesrepId) {
                $salesrepId = $selectedSalesrepId;
            } else {
                $customer = $quote->getCustomer();
                if ($customer->getId()) {
                    $customAttr = $customer->getCustomAttributes();
                    if (isset($customAttr['salesrep_rep_id'])) {
                        $salesrepIdData = $customAttr['salesrep_rep_id'];
                        $salesrepId = $salesrepIdData->getValue();
                    }
                }
            }

            if ($salesrepId) {
                $salesrepModel = $this->salesrepRepositoryInterface->get();
                $salesrepModel
                    ->setOrderId($order->getId())
                    ->setRepId($salesrepId);

                $adminUser = $this->adminUsers->getItemById($salesrepId);

                $this->salesrepRepositoryInterface->save($salesrepModel);

                if ($adminUser && $adminUser->getUserId()) {
                    $adminName = $adminUser->getFirstname() . ' ' . $adminUser->getLastname();

                    $salesrepModel = $this->salesrepRepositoryInterface
                        ->getByOrderId($order->getId());

                    $salesrepModel->setRepName($adminName);

                    $salesrepCommissionEarned = $this->salesrepRepositoryInterface
                        ->getRepCommissionEarned(
                            $order->getId(),
                            $adminUser->getSalesrepRepCommissionRate()
                        );

                    if ($salesrepCommissionEarned != null) {
                        $salesrepModel->setRepCommisionEarned(
                            $salesrepCommissionEarned
                        );
                    }

                    $salesrepModel->setRepCommisionStatus($defaultStatus);

                    if ($adminUser->getSalesrepManagerId()) {
                        $managerData = $this->adminUsers->getItemById(
                            $adminUser->getSalesrepManagerId()
                        );

                        if ($managerData && $managerData->getUserId()) {
                            $salesrepModel->setManagerId($managerData->getUserId());

                            $managerName = $managerData->getFirstname()
                                . ' ' . $managerData->getLastname();

                            $salesrepModel->setManagerName($managerName);

                            $managerCommission = $this->salesrepRepositoryInterface
                                ->getManagerCommissionEarned(
                                    $order->getId(),
                                    $managerData->getSalesrepManagerCommissionRate(),
                                    $salesrepCommissionEarned
                                );

                            if ($managerCommission != null) {
                                $salesrepModel->setManagerCommissionEarned(
                                    $managerCommission
                                );
                            }
                            $salesrepModel->setManagerCommissionStatus(
                                $defaultStatus
                            );
                        }
                    }
                }
                $this->salesrepRepositoryInterface->save($salesrepModel);
                $this->checkoutSession->setSelectedSalesrepId('');
            }
        }
    }
}
