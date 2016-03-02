<?php

namespace Buzz\Client;
use Rhumsaa\Uuid\Uuid;
use Rhumsaa\Uuid\Exception\UnsatisfiedDependencyException;

abstract class AbstractClient implements ClientInterface
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;
    protected $verifyPeer = true;
    protected $verifyHost = 2;
    protected $proxy;

    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
    }

    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;
    }

    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setVerifyPeer($verifyPeer)
    {
        $this->verifyPeer = $verifyPeer;
    }

    public function getVerifyPeer()
    {
        return $this->verifyPeer;
    }

    public function getVerifyHost()
    {
        return $this->verifyHost;
    }

    public function setVerifyHost($verifyHost)
    {
        $this->verifyHost = $verifyHost;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    //If headers doesn't have Cid. it will add Cid
    function addCid($request){
        $headers = $request->getHeaders();
        if(empty($headers)){
            $cid=$this->generateCid();
            $headers=array($cid);
        }else{
            $cidPresent=false;
            foreach ($headers as $value) {
                if(strpos($value, 'Cid:')  !== false){
                    $cidPresent = true;
                    break;
                }
            }
            if(! $cidPresent){
                $cid=$this->generateCid();      
                $headers[]=$cid;
             }
        }
        $request->setHeaders($headers);
        return $request;
    }
}
