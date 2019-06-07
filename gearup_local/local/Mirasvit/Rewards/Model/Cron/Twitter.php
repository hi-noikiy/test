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
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



use Abraham\TwitterOAuth\TwitterOAuth;

class Mirasvit_Rewards_Model_Cron_Twitter
{
    /**
     * @return int
     */
    public function checkTwitterRules()
    {
        $rules = Mage::getModel('rewards/earning_rule')->getCollection()
            ->addFieldToFilter('is_active', true)
            ->addFieldToFilter('type', Mirasvit_Rewards_Model_Config::TYPE_BEHAVIOR)
            ->addFieldToFilter('behavior_trigger', Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TWITTER_TWEET);

        return count($rules);
    }

    /**
     *
     */
    public function run()
    {
        require_once Mage::getBaseDir('lib') . '/Mirasvit/Twitteroauth/autoload.php';
        $this->earnTwitterPoints();
    }

    /**
     *
     */
    public function earnTwitterPoints()
    {
        /** @var Mirasvit_RewardsSocial_Model_Config $config */
        $config = Mage::getSingleton('rewardssocial/config');

        $key = trim($config->getTwitterConsumerKey());
        $secret = trim($config->getTwitterConsumerSecret());

        if ($key === '' || $secret === '' || !$this->checkTwitterRules()) {
            return;
        }

        $connection = new TwitterOAuth(
            $key,
            $secret
        );

        $connection->setDecodeJsonAsArray(true);

        $data = $connection->oauth2('oauth2/token', array('grant_type' => 'client_credentials'));

        if (isset($data['token_type']) && $data['token_type'] == 'bearer') {
            $config->setTwitterToken($data['access_token']);
            $config->setTwitterIsTokenActive(true);
        }

        $connection->setDecodeJsonAsArray(false);

        $collection = Mage::getModel('rewards/earning_rule_queue')->getCollection()
            ->addFieldToFilter('is_processed', 0);

        /** @var Mirasvit_Rewards_Model_Resource_Earning_Rule_Queue_Collection $item */
        foreach ($collection as $item) {
            $search = urldecode(str_replace($item->getRuleType().'-', '', $item->getRuleCode()));
            $customer = Mage::getModel('customer/customer')->load($item->getCustomerId());
            $date = explode(' ', $item->getCreatedAt());
            $since = $date[0];
            $connection = new TwitterOAuth(
                $config->getTwitterConsumerKey(),
                $config->getTwitterConsumerSecret(),
                null,
                $config->getTwitterToken()
            );
            $data = $connection->get(
                'search/tweets', array(
                    'q' => $search,
                    'since' => $since,
                )
            );

            if (!empty($data->statuses)) {
                if (count($data->statuses)) {
                    $transaction = Mage::helper('rewards/behavior')->processRule(
                        $item->getRuleType(),
                        $customer,
                        $item->getWebsiteId(),
                        $search,
                        array()
                    );

                    if ($transaction) {
                        $item->setIsProcessed(1)->save();
                    }
                }
            }
        }
    }
}
