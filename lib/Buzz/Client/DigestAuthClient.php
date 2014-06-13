<?php

namespace Buzz\Client;

use Buzz\Message\RequestInterface;
use Buzz\Message\MessageInterface;

class DigestAuthClient extends AbstractDecoratorClient
{
    private $username;
    private $password;
    private $realm;

    private $algorithm;
    private $authenticationMethod;
    private $clientNonce;
    private $domain;
    private $entityBody;
    private $method;
    private $nonce;
    private $nonceCount;
    private $opaque;
    private $uri;

    public function postSend(RequestInterface $request, MessageInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $this->parseServerHeaders($response->getHeaders(), $statusCode);
        if($statusCode == 401) {
            $request->addHeader($this->getHeader());
            $this->resend($request, $response);
        }
    }

    public function preSend(RequestInterface $request)
    {
        $this->setUri($request->getResource());
        $this->setMethod($request->getMethod());
        $this->setEntityBody($request->getContent());

        $header = $this->getHeader();
        if($header) {
            $request->addHeader($header);
        }
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setRealm($realm)
    {
        $this->realm = $realm;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    private function getAlgorithm()
    {
        if($this->algorithm == null) {
            $this->algorithm = 'MD5';
        }
        return $this->algorithm;
    }

    private function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }

    private function getClientNonce()
    {
        if($this->clientNonce == null) {
            $this->clientNonce = uniqid();
            $this->incrementNonceCount();
        }
        return $this->clientNonce;
    }

    private function getDomain()
    {
        return $this->domain;
    }

    private function getEntityBody()
    {
        return (string)$this->entityBody;
    }

    private function getHA1()
    {
        $username = $this->getUsername();
        $password = $this->getPassword();
        $realm = $this->getRealm();

        if(($username) AND ($password) AND ($realm)) {
            $algorithm = $this->getAlgorithm();

            if(!isset($algorithm) OR ($algorithm == "MD5")) {

                $A1 = "{$username}:{$realm}:{$password}";
            }
            if($this->algorithm == "MD5-sess") {

                $nonce = $this->getNonce();
                $cnonce = $this->getClientNonce();
                if(($nonce) AND ($cnonce)) {
                    $A1 = $this->hash("{$username}:{$realm}:{$password}") . ":{$nonce}:{$cnonce}";              
                }
            }
            if(isset($A1)) {
                $HA1 = $this->hash($A1);
                return $HA1;
            }
        }
        return null;
    }

    private function getHA2()
    {
        $method = $this->getMethod();
        $uri = $this->getUri();

        if(($method) AND ($uri)) {
            $qop = $this->getQOP();

            if(!isset($qop) OR ($qop == 'auth')) {
                $A2 = "{$method}:{$uri}";
            }
            if($qop == 'auth-int') {
                $entityBody = $this->getEntityBody();
                $A2 = "{$method}:{$uri}:" . $this->hash($entityBody);
            }

            if(isset($A2)) {
                $HA2 = $this->hash($A2);
                return $HA2;
            }           
        }
        return null;
    }

    private function getHeader()
    {
        if($this->getAuthenticationMethod() == 'Digest') {
            $username = $this->getUsername();
            $realm = $this->getRealm();
            $nonce = $this->getNonce();
            $response = $this->getResponse();
            if(($username) AND ($realm) AND ($nonce) AND ($response)) {
                $uri = $this->getUri();
                $opaque = $this->getOpaque();
                $domain = $this->getDomain();
                $qop = $this->getQOP();

                $header = "Authorization: Digest";
                $header .= " username=\"" . $username . "\",";
                $header .= " realm=\"" . $realm . "\",";
                $header .= " nonce=\"" . $nonce . "\",";
                $header .= " response=\"" . $response . "\",";

                if($uri) {
                    $header .= " uri=\"" . $uri . "\",";
                }
                if($opaque) {
                    $header .= " opaque=\"" . $opaque . "\",";
                }
                if($domain) {
                    $header .= " domain=\"" . $domain . "\",";
                }

                if($qop) {
                    $header .= " qop=" . $qop . ",";
                
                    $cnonce = $this->getClientNonce();
                    $nc = $this->getNonceCount();

                    if($cnonce) {
                        $header .= " nc=" . $nc . ",";
                    }
                    if($cnonce) {
                        $header .= " cnonce=\"" . $cnonce . "\",";
                    }
                }

// Remove the last comma from the header
                $header = substr($header, 0, strlen($header) - 1);
                return $header;
            }
        }
        if($this->getAuthenticationMethod() == 'Basic') {
            $username = $this->getUsername();
            $password = $this->getPassword();
            if(($username) AND ($password)) {
                $header = 'Authorization: Basic ' . base64_encode("{$username}:{$password}");
                return $header;
            }
        }
        return null;
    }

    private function getMethod()
    {
        return $this->method;
    }

    private function getNonce()
    {
        return $this->nonce;
    }

    private function getNonceCount()
    {
        return $this->nonceCount;
    }

    private function getOpaque()
    {
        return $this->opaque;
    }

    private function getPassword()
    {
        return $this->password;
    }

    private function getRealm()
    {
        return $this->realm;
    }

    private function getResponse()
    {
        $HA1 = $this->getHA1();
        $nonce = $this->getNonce();
        $HA2 = $this->getHA2();

        if(($HA1) AND ($nonce) AND ($HA2)) {
            $qop = $this->getQOP();

            if(!isset($qop)) {
                $response = $this->hash("{$HA1}:{$nonce}:{$HA2}");
                return $response;
            } else {
                $cnonce = $this->getClientNonce();
                $nc = $this->getNonceCount();
                if(($cnonce) AND ($nc)) {
                    $response = $this->hash("{$HA1}:{$nonce}:{$nc}:{$cnonce}:{$qop}:{$HA2}");
                    return $response;
                }
            }
        }
        return null;
    }

    private function getQOP()
    {
        return $this->qop[0];
    }

    private function getUsername()
    {
        return $this->username;
    }

    private function getUri()
    {
        return $this->uri;
    }

    private function hash($value)
    {
        $algorithm = $this->getAlgorithm();
        if(($algorithm == 'MD5') OR ($algorithm == 'MD5-sess')) {
            return hash('md5', $value);
        }
    }

    private function incrementNonceCount()
    {
        if($this->nonceCount == null) {
            $this->nonceCount = '00000001';
        } else {
            $this->nonceCount++;
        }
    }

    private function parseAuthenticationInfoHeader($authenticationInfo)
    {
// Remove "Authentication-Info: " from start of header
        $wwwAuthenticate = substr($wwwAuthenticate, 21, strlen($wwwAuthenticate) - 21);

        $nameValuePairs = $this->parseNameValuePairs($wwwAuthenticate);
        foreach($nameValuePairs as $name => $value) {
            switch($name) {
                case 'message-qop':

                break;
                case 'nextnonce':
// This function needs to only set the Nonce once the rspauth has been verified.
                    $this->setNonce($value);
                break;
                case 'rspauth':
// Check server rspauth value
                break;
            }
        }
    }

    private function parseNameValuePairs($nameValuePairs)
    {
        $parsedNameValuePairs = array();
        $nameValuePairs = explode(',', $nameValuePairs);
        foreach($nameValuePairs as $nameValuePair) {
// Trim the Whitespace from the start and end of the name value pair string
            $nameValuePair = trim($nameValuePair);
// Split $nameValuePair (name=value) into $name and $value
            list($name, $value) = explode('=', $nameValuePair, 2);
// Remove quotes if the string is quoted
            $value = $this->unquoteString($value);
// Add pair to array[name] => value
            $parsedNameValuePairs[$name] = $value;
        }
        return $parsedNameValuePairs;
    }

    private function parseServerHeaders(array $headers)
    {
        foreach($headers as $header) {
// Check to see if the WWW-Authenticate header is present and if so set $authHeader
            if(substr(strtolower($header), 0, 18) == 'www-authenticate: ') {
                $wwwAuthenticate = $header;
                $this->parseWwwAuthenticateHeader($wwwAuthenticate);
            }
// Check to see if the Authentication-Info header is present and if so set $authInfo
            if(substr(strtolower($header), 0, 21) == 'authentication-info: ') {
                $authenticationInfo = $header;
                $this->parseAuthenticationInfoHeader($wwwAuthenticate);
            }
        }
    }

    private function parseWwwAuthenticateHeader($wwwAuthenticate)
    {
// Remove "WWW-Authenticate: " from start of header
        $wwwAuthenticate = substr($wwwAuthenticate, 18, strlen($wwwAuthenticate) - 18);
        if(substr($wwwAuthenticate, 0, 7) == 'Digest ') {
            $this->setAuthenticationMethod('Digest');
// Remove "Digest " from start of header
            $wwwAuthenticate = substr($wwwAuthenticate, 7, strlen($wwwAuthenticate) - 7);

            $nameValuePairs = $this->parseNameValuePairs($wwwAuthenticate);

            foreach($nameValuePairs as $name => $value) {
                switch($name) {
                    case 'algorithm':
                        $this->setAlgorithm($value);
                    break;
                    case 'domain':
                        $this->setDomain($value);
                    break;
                    case 'nonce':
                        $this->setNonce($value);
                    break;
                    case 'realm':
                        $this->setRealm($value);
                    break;
                    case 'opaque':
                        $this->setOpaque($value);
                    break;
                    case 'qop':
                        $this->setQOP(explode(',', $value));
                    break;
                }
            }
        }
        if (substr($wwwAuthenticate, 0, 6) == 'Basic ') {
            $this->setAuthenticationMethod('Basic');
// Remove "Basic " from start of header
            $wwwAuthenticate = substr($wwwAuthenticate, 6, strlen($wwwAuthenticate) - 6);

            $nameValuePairs = $this->parseNameValuePairs($wwwAuthenticate);

            foreach($nameValuePairs as $name => $value) {
                switch($name) {
                    case 'realm':
                        $this->setRealm($value);
                    break;
                }
            }
        }
    }

    private function setAlgorithm($algorithm)
    {
        if(($algorithm == 'MD5') OR ($algorithm == 'MD5-sess')) {
            $this->algorithm = $algorithm;
        } else {
            throw new \InvalidArgumentException('DigestAuthAdapter: Only MD5 and MD5-sess algorithms are currently supported.');
        }
    }

    private function setAuthenticationMethod($authenticationMethod)
    {
        if(($authenticationMethod == 'Digest') OR ($authenticationMethod == 'Basic')) {
            $this->authenticationMethod = $authenticationMethod;
        } else {
            throw new \InvalidArgumentException('DigestAuthAdapter: Only Digest and Basic authentication methods are currently supported.');
        }
    }

    private function setDomain($value)
    {
        $this->domain = $value;
    }

    private function setEntityBody($entityBody = null)
    {
        $this->entityBody = $entityBody;
    }

    private function setMethod($method = null)
    {
        $this->method = $method;
    }

    private function setNonce($nonce = null)
    {
        $this->nonce = $nonce;
    }

    private function setOpaque($opaque)
    {
        $this->opaque = $opaque;
    }

    private function setQOP(array $qop = array())
    {
        $this->qop = $qop;
    }

    private function setUri($uri = null)
    {
        $this->uri = $uri;
    }

    private function unquoteString($str = null)
    {
        if($str) {
            if(substr($str, 0, 1) == '"') {
                $str = substr($str, 1, strlen($str) - 1);
            }
            if(substr($str, strlen($str) - 1, 1) == '"') {
                $str = substr($str, 0, strlen($str) - 1);
            }
        }
        return $str;
    }
}