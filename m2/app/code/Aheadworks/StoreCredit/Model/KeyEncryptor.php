<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class KeyEncryptor
 *
 * @package Aheadworks\StoreCredit\Model
 */
class KeyEncryptor
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt external key
     *
     * @param string $customerEmail
     * @param int $customerId
     * @param int $websiteId
     * @return string
     */
    public function encrypt($customerEmail, $customerId, $websiteId)
    {
        return base64_encode($this->encryptor->encrypt($customerEmail . ',' . $customerId . ',' . $websiteId));
    }

    /**
     * Decrypt external key
     *
     * @param string $key
     * @return string
     */
    public function decrypt($key)
    {
        list($email, $customerId, $websiteId) = explode(',', $this->encryptor->decrypt(base64_decode($key)));
        return ['email' => $email, 'customer_id' => $customerId, 'website_id' => $websiteId];
    }
}
