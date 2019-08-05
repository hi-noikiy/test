<?php
namespace Magenest\Salesforce\Model\Sync;

class DataGetter
{
    protected $_job;

    public function __construct(
        Job $job
    ) {
        $this->_job = $job;
    }

    /**
     * return an array of contacts on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceContacts()
    {
        $query = 'SELECT Id, Email FROM Contact';
        $result = $this->_job->sendBatchRequest('query', 'Contact', $query);
        return $result;
    }

    /**
     * return an array of accounts on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceAccounts()
    {
        $query = 'SELECT Id, Name FROM Account';
        $result = $this->_job->sendBatchRequest('query', 'Account', $query);
        return $result;
    }

    /**
     * return an array of leads on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceLeads()
    {
        $query = 'SELECT Id, Email FROM Lead';
        $result = $this->_job->sendBatchRequest('query', 'Lead', $query);
        return $result;
    }

    /**
     * return an array of products on Salesforce
     * @return mixed|string
     */
    public function getAllSalesforceProducts()
    {
        $query = 'SELECT Id, ProductCode FROM Product2';
        $result = $this->_job->sendBatchRequest('query', 'Product2', $query);
        return $result;
    }

    /**
     * return an array of pricebook entries on Salesforce
     * @return mixed|string
     */
    public function getAllPricebookEntry()
    {
        $query = "SELECT Id, ProductCode FROM PricebookEntry ORDER BY Id";
        $result = $this->_job->sendBatchRequest('query', 'PricebookEntry', $query);
        return $result;
    }

    /**
     * return an array of Orders entries on Salesforce
     * @return mixed|string
     */
    public function getAllSalesforceOrders()
    {
        $query = "SELECT Id, PoNumber FROM Order";
        $result = $this->_job->sendBatchRequest('query', 'Order', $query);
        return $result;
    }

    /**
     * return an array of Opportunities on Salesforce
     * @return mixed|string
     */
    public function getAllSalesforceOpportunities()
    {
        $query = "SELECT Id, NAME FROM Opportunity";
        $result = $this->_job->sendBatchRequest('query', 'Opportunity', $query);
        return $result;
    }

    /**
     * return an array of Campaigns on Salesforce
     * @return mixed|string
     */
    public function getAllSalesforceCampaigns()
    {
        $query = "SELECT Id,Name FROM Campaign";
        $result = $this->_job->sendBatchRequest('query', 'Campaign', $query);
        return $result;
    }
}
