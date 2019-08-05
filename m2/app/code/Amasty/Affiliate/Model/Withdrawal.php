<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\TransactionInterface;

class Withdrawal extends \Amasty\Affiliate\Model\Transaction implements TransactionInterface
{
    /**
     * @param $requestedAmount
     */
    public function create($requestedAmount)
    {
        $currentAccount = $this->accountRepository->getCurrentAccount();

        $data = [
                'affiliate_account_id' => $currentAccount->getAccountId(),
                'commission' => $requestedAmount,
                'type' => self::TYPE_WITHDRAWAL,
                'status' => self::STATUS_PENDING,
                'balance' => $this->accountRepository->getCurrentAccount()->getBalance()
            ];
        $this->setData($data);

        $this->withdrawalRepository->save($this);
        $this->sendMail($requestedAmount);
    }

    public function repeat()
    {
        $this->setTransactionId(null);
        $this->setBalance($this->accountRepository->getCurrentAccount()->getBalance());
        $this->setStatus(self::STATUS_PENDING);
        $this->setUpdatedAt(null);

        $this->withdrawalRepository->save($this);
        $this->sendMail($this->getCommission());
    }

    /**
     * @param $requestedAmount
     */
    protected function sendMail($requestedAmount)
    {
        /** @var \Amasty\Affiliate\Model\Account $currentAccount */
        $currentAccount = $this->accountRepository->getCurrentAccount();

        if ($this->scopeConfig->getValue('amasty_affiliate/email/admin/withdrawal_request')) {
            $emailData = $currentAccount->getData();
            $emailData['name'] = $currentAccount->getFirstname() . ' ' . $currentAccount->getLastname();
            $emailData['amount'] = $currentAccount->convertToPrice($requestedAmount);
            $emailData['balance'] = $currentAccount->convertToPrice($currentAccount->getBalance());
            $sendToMail = $this->scopeConfig->getValue('amasty_affiliate/email/general/recipient_email');

            $this->mailsender->sendMail($emailData, Mailsender::TYPE_ADMIN_NEW_WITHDRAWAL, $sendToMail);
        }
    }
}
