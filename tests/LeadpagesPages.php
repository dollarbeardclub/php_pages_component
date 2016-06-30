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
        $this->pageId = getenv('pageId');

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
        $response = $this->pages->getSinglePageDownloadUrl($this->pageId);
        $responseArray = json_decode($response['response'], true);

        $parsedUrl = parse_url($responseArray['url']);

        $this->assertEquals('https', $parsedUrl['scheme']);
        $this->assertGreaterThan(0, strpos($parsedUrl['host'], 'leadpages.co'));

    }

    public function testDownloadPageHtml()
    {
        $response = $this->pages->downloadPageHtml($this->pageId);
        $this->assertEquals('200', $response['code']);
        $this->assertContains('This beautiful and lightning fast landing page was proudly created with Leadpages', $response['response']);
    }

    public function test_download_page_html_fail_bad_id()
    {

        $response = $this->pages->downloadPageHtml('badid');

        $this->assertEquals('0', $response['code']);
        $this->assertTrue($response['error']);
    }

    public function test_download_page_html_fail_no_token()
    {
        $this->login->token = NULL;
        $response = $this->pages->downloadPageHtml('badid');
        $this->assertEquals('0', $response['code']);
        $this->assertTrue($response['error']);
    }

}