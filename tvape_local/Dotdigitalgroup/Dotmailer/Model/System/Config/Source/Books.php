<?php

class Dotdigitalgroup_Dotmailer_Model_System_Config_Source_Books
{

    public function toOptionArray()
    {
		$config = Mage::getStoreConfig('dotmailer');
		$ListAddressBooks = array();
		$SoapClient = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
		$ListAddressBooks[] = array('value' => NULL, 'label'=> "Please, select address book...");
		try {
			$books =  $SoapClient->ListAddressBooks(array('username' => $config['dotMailer_group']['dotMailer_api_username'],'password' => $config['dotMailer_group']['dotMailer_api_password']))->ListAddressBooksResult->APIAddressBook;
			if($books)
				if(is_array($books))
					foreach($books as $book)
						$ListAddressBooks[] = array('value' => $book->ID, 'label'=> $book->Name);
				else
					$ListAddressBooks[] = array('value' => $books->ID, 'label'=> $books->Name);
			
		} catch (SoapFault $fault) {
		}
		
		return $ListAddressBooks;
    }

}
