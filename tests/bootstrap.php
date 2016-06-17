<?php

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
require_once dirname(dirname(__FILE__)).'/src/Pages/LeadpagesPages.php';


$localTestDataFile = dirname(__FILE__) . '/data/testData.php';
if(file_exists($localTestDataFile)) {
    require $localTestDataFile;
}

use GuzzleHttp\Client;
use Leadpages\Auth\LeadpagesLogin;

class fakeLogin extends LeadpagesLogin
{


    /**
     * act as our database
     * @var array
     */
    public $datastore = [];
    public $username;
    public $password;


    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->username = getenv('username');
        $this->password = getenv('password');
        //$this->token = getenv('testToken');
        //chris ceccoli token has lots of pages
        $this->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJsZWFkcGFnZXMubmV0IiwiaXNzIjoiYXBpLmxlYWRwYWdlcy5pbyIsImFjY2Vzc0lkIjoiRHhmQTVxS1lXNk5hY2UyOXVqRDdDUCIsInNlc3Npb25JZCI6Inc4b2hTTlpMb1lqaHZLeVVQdE5vbmkiLCJleHAiOjE0Njg3NzIwODYsImlhdCI6MTQ2NjE4MDA4Nn0.TQo07Q7QkWmkPbXNkoJTk1vGD-TjyI6g7pzPwcz6PXU';
    }


    /**
     * method to implement to store token in database
     *
     * @return mixed
     */
    public function storeToken()
    {
        $this->datastore['token'] = $this->token;
    }

    /**
     * method to implement to get token from datastore
     * should return token not set property of $this->token
     * @return mixed
     */
    public function getToken()
    {
        return $this->datastore['token'];
    }

    /**
     * method to implement to remove token from database
     * @return mixed
     */
    public function deleteToken()
    {
        unset($this->datastore['token']);
    }

    /**
     * method to check if token is empty
     *
     * @return mixed
     */
    public function checkIfTokenIsEmpty()
    {
        if(!isset($this->datastore['token'])){
            return false;
        }else{
            return true;
        }
    }
}