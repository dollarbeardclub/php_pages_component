<?php


use GuzzleHttp\Client;
use Leadpages\Pages\LeadpagesPages;

class TestLeadpagesPagesSuccess extends PHPUnit_Framework_TestCase
{

    public $pages;
    public $login;
    public $client;

    public function setUp(){


        $this->client = new Client();
        $this->login  = new fakeLogin($this->client);
        $this->pages  = new LeadpagesPages($this->client, $this->login);

    }

    public function testGetAllPages()
    {   $startTime = microtime(true);
        $response = $this->pages->getAllUserPages();
        $finishTime = microtime(true);
        $totalTime = $finishTime - $startTime;
        $this->assertNotEmpty($response['_items']);
        //a test for time the api call takes 10 seconds is probably way to high
        //$this->assertLessThan(10, $totalTime);
    }

    public function testGetSinglePageDownloadUrl()
    {
        $response = $this->pages->getSinglePageDownloadUrl('5691563690688512');
        $responseArray = json_decode($response['response'], true);

        $parsedUrl = parse_url($responseArray['url']);

        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertGreaterThan(0, strpos($parsedUrl['host'], 'leadpages.co'));

    }

    public function testDownloadPageHtml()
    {
        $html = $this->pages->downloadPageHtml('5691563690688512');
        echo $html;
    }

}