<?php

namespace Buzz\Test\Client;
use Buzz\Message\Request;
use Buzz\Client\Cid;
use Buzz\Browser;

class CidTest extends \PHPUnit_Framework_TestCase
{
    public function testAddCidByLib()
    {
        $request = new Request();
        $cidObj = new Cid();
        $request = $cidObj->addCid($request);
        $headers = $request->getHeaders();
        $cidHeader=$headers[0];
        $cidAdded=false;
         if(strpos(strtolower($cidHeader), 'cid:')  !== false){
            $cidAdded=true;
         }
        $this->assertEquals($cidAdded, true);
    }

    public function testAddExplicitCid()
    {
        $request = new Request();
        $headers = array(
            'Cid' => '123'
        );
        $request->setHeaders($headers);
        $cidObj = new Cid();
        $request = $cidObj->addCid($request);
        $headers = $request->getHeaders();
        $cidHeader=$headers[0];   
        $this->assertEquals($cidHeader, "Cid: 123");
    }

    public function testGetRequestWithoutCid()
    {
        $browser = new Browser();
        $response = $browser->get('http://127.0.0.1:8080/server.php');
        $this->assertEquals($response->getStatusCode(), 200);
    }

    public function testGetRequestWithCid()
    {
        $browser = new Browser();
        $headers = array(
            'Cid' => '123'
        );
        $response = $browser->get('http://127.0.0.1:8080/server.php', $headers);
        $this->assertEquals($response->getStatusCode(), 200);
    }
}    
