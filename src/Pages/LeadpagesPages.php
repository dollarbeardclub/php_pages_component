<?php


namespace Leadpages\Pages;

use GuzzleHttp\Client;
use Leadpages\Auth\LeadpagesLogin;
use GuzzleHttp\Exception\ClientException;

class LeadpagesPages
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * @var \Leadpages\Auth\LeadpagesLogin
     */
    private $login;
    /**
     * @var \Leadpages\Auth\LeadpagesLogin
     */
    public $response;

    public function __construct(Client $client, LeadpagesLogin $login)
    {

        $this->client = $client;
        $this->login = $login;
        $this->PagesUrl = "https://my.leadpages.net/page/v1/pages";
    }

    public function getPages($cursor  = false)
    {
        if(!$cursor) {
            $queryArray = [];
        }else{
            $queryArray = ['cursor' => $cursor];
        }

        try{
            $response = $this->client->get($this->PagesUrl,
              [
                'headers' => ['LP-Security-Token' => $this->login->token],
                'query' => $queryArray
              ]);
            $response       = [
              'code'     => '200',
              'response' => $response->getBody(),
              'error'    => (bool)false
            ];
        }catch (ClientException $e){
            $response       = [
              'code'     => $e->getCode(),
              'response' => $e->getMessage(),
              'error'    => (bool)true
            ];
        }

        return $response;

    }

    public function getAllUserPages($returnResponse = array(), $cursor = false){

        //get & parse response
        $response = $this->getPages($cursor);
        $response = json_decode($response['response'], true);

        if(empty($response['_items'])){
            echo'<p><strong>You appear to have no Leadpages created yet.</strong></p>';
            echo '<p> Please login to <a href="https://my.leadpages.net" target="_blank">Leadpages</a> and create a Leadpage to continue.</p>';
            die();
        }

        //if we have more pages add these pages to returnResponse and pass it back into this method
        //to run again
        if($response['_meta']['hasMore'] == true){
            $returnResponse[] = $response['_items'];
            return $this->getAllUserPages($returnResponse, $response['_meta']['nextCursor']);
        }

        //once we run out of hasMore pages return the response with all pages returned
        if (!$response['_meta']['hasMore']) {
            /**
             * add last result to return response
             */
            $returnResponse[] = $response['_items'];

            /**
             * this maybe a bit hacky but for recursive and compatibility with other functions
             * needed all items to be under one array under _items array
             */
            //echo '<pre>';print_r($returnResponse);die();

            if (isset($returnResponse) && sizeof($returnResponse) > 0) {
                $pages = array(
                  '_items' => array()
                );
                foreach ($returnResponse as $subarray) {
                    $pages['_items'] = array_merge($pages['_items'], $subarray);
                }

                //strip out unpublished pages
                //sort pages asc by name
                $pages = $this->sortPages($this->stripB3NonPublished($pages));

                return $pages;
            }
        }
    }


    public function stripB3NonPublished($pages)
    {
        foreach($pages['_items'] as $index => $page){
            if($page['isBuilderThreePage'] && !$page['isBuilderThreePublished']){
                unset($pages['_items'][$index]);
            }
        }

        return $pages;
    }

    public function sortPages($pages)
    {
        usort($pages['_items'], function($a, $b){
            return strcmp($a["name"], $b["name"]);
        });

        return $pages;
    }

    public function getSinglePageDownloadUrl($pageId)
    {
        try{
            $response = $this->client->get($this->PagesUrl.'/'.$pageId,
              [
                'headers' => ['LP-Security-Token' => $this->login->token],
              ]);

            $body = json_decode($response->getBody(), true);
            $url  = $body['_meta']['publishUrl'];
            $responseText = ['url' => $url];

            $response       = [
              'code'     => '200',
              'response' => json_encode($responseText),
              'error'    => (bool)false
            ];
        }catch (ClientException $e){
            $response       = [
              'code'     => $e->getCode(),
              'response' => $e->getMessage(),
              'error'    => (bool)true
            ];
        }

        return $response;
    }

    /**
     * get url for page, then use a get request to get the html for the page
     * TODO at sometime this should be replaced with a single call to get the html this requires to calls
     * @param $pageId
     *
     * @return mixed
     */
    public function downloadPageHtml($pageId){
        try {
            if (is_null($this->login->token)) {
                $this->login->token = $this->login->getAccessToken();
            }
        }catch(Exception $e){
            echo $e->getMessage();
        }

        $response = $this->getSinglePageDownloadUrl($pageId);
        $responseArray = json_decode($response['response'], true);
        die($responseArray['url']);
        $html = $this->client->get($responseArray['url']);
        return $html;
    }

}