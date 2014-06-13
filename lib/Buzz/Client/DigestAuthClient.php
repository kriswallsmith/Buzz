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

    /**
     *
     * QOP options: Only one of the following can be set at any time. setOptions will throw an exception otherwise.
     * OPTION_QOP_BEST_AVAILABLE - Use best available QOP (auth-int) if available, fallback to auth if auth-int not available.
     * OPTION_QOP_AUTH_INT       - Always use auth-int   (if available)
     * OPTION_QOP_AUTH           - Always use auth       (even if auth-int available)
     */
    const OPTION_QOP_BEST_AVAILABLE       = 1;
    const OPTION_QOP_AUTH_INT             = 2;
    const OPTION_QOP_AUTH                 = 4;
    /**
     * Ignore server request to downgrade authentication from Digest to Basic.
     * Breaks RFC compatibility, but ensures passwords are never sent using base64 which is trivial for an attacker to decode.
     */
    const OPTION_IGNORE_DOWNGRADE_REQUEST = 8;
    /**
     * Discard Client Nonce on each request.
     */
    const OPTION_DISCARD_CLIENT_NONCE     = 16;

    private $options;

    /**
     * Set OPTION_QOP_BEST_AVAILABLE and OPTION_DISCARD_CLIENT_NONCE by default.
     */
    public function __construct(ClientInterface $client)
    {
        $this->options = DigestAuthClient::OPTION_QOP_BEST_AVAILABLE || DigestAuthClient::OPTION_DISCARD_CLIENT_NONCE;
        parent::__construct($client);
    }

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

    /**
     * Sets the password to be used to authenticate the client.
     *
     * @param string $password The password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Sets the realm to be used to authenticate the client.
     *
     * @param string $realm The realm
     *
     * @return void
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
    }

    /**
     * Sets the username to be used to authenticate the client.
     *
     * @param string $username The username
     *
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setOptions($options)
    {

    }

    private function discardClientNonce()
    {
        $this->clientNonce = null;
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

            if($this->nonceCount == null) {
// If nonceCount is not set then set it to 00000001.
                $this->nonceCount = '00000001';
            } else {
// If it is set then increment it.
                $this->nonceCount++;
            }
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
// Discard the Client Nonce if OPTION_DISCARD_CLIENT_NONCE is set.
                if(($this->options || DigestAuthClient::OPTION_DISCARD_CLIENT_NONCE) === true) {
                    $this->discardClientNonce();
                }
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

    /**
     * Calculates the hash for a given value using the algorithm specified by the server.
     *
     * @param string $value The value to be hashed
     *
     * @return string The hashed value.
     */
    private function hash($value)
    {
        $algorithm = $this->getAlgorithm();
        if(($algorithm == 'MD5') OR ($algorithm == 'MD5-sess')) {
            return hash('md5', $value);
        }
        return null;
    }

    /**
     * Parses the Authentication-Info header received from the server and calls the relevant setter method on each variable received.
     *
     * @param string $authenticationInfo The full Authentication-Info header.
     *
     * @return void
     */
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

    /**
     * Parses a string of name=value pairs separated by commas and returns and array with the name as the index.
     *
     * @param string $nameValuePairs The string containing the name=value pairs.
     *
     * @return array An array with the name used as the index and the values stored within.
     */
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

    /**
     * Parses the server headers received and checks for WWW-Authenticate and Authentication-Info headers.
     * Calls parseWwwAuthenticateHeader() and parseAuthenticationInfoHeader() respectively if either of these headers are present.
     *
     * @param array $headers An array of the headers received by the client.
     *
     * @return void
     */
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

    /**
     * Parses the WWW-Authenticate header received from the server and calls the relevant setter method on each variable received.
     *
     * @param string $wwwAuthenticate The full WWW-Authenticate header.
     *
     * @return void
     */
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

    /**
     * Sets the hashing algorithm to be used. Currently only uses MD5 specified by either MD5 or MD5-sess.
     * RFCs are currently in draft stage for the proposal of SHA-256 and SHA-512-256.
     * Support will be added once the RFC leaves the draft stage.
     *
     * @param string $algorithm The algorithm the server has requested to use.
     *
     * @throws \InvalidArgumentException If $algorithm is set to anything other than MD5 or MD5-sess.
     *
     * @return void
     */
    private function setAlgorithm($algorithm)
    {
        if(($algorithm == 'MD5') OR ($algorithm == 'MD5-sess')) {
            $this->algorithm = $algorithm;
        } else {
            throw new \InvalidArgumentException('DigestAuthClient: Only MD5 and MD5-sess algorithms are currently supported.');
        }
    }

    /**
     * Sets authentication method to be used. Options are "Digest" and "Basic".
     * If the server and the client are unable to authenticate using Digest then the RFCs state that the server should attempt
     * to authenticate the client using Basic authentication. This ensures that we adhere to that behaviour.
     * This does however create the possibilty of a downgrade attack so it may be an idea to add a way of disabling this functionality
     * as Basic authentication is trivial to decrypt and exposes the username/password to a man-in-the-middle attack.
     *
     * @param string $authenticationMethod The authentication method requested by the server.
     *
     * @throws \InvalidArgumentException If $authenticationMethod is set to anything other than Digest or Basic
     *
     * @return void
     */
    private function setAuthenticationMethod($authenticationMethod)
    {
        if(($authenticationMethod == 'Digest') OR ($authenticationMethod == 'Basic')) {
            $this->authenticationMethod = $authenticationMethod;
        } else {
            throw new \InvalidArgumentException('DigestAuthClient: Only Digest and Basic authentication methods are currently supported.');
        }
    }

    /**
     * Sets the domain to be authenticated against. THIS IS NOT TO BE CONFUSED WITH THE HOSTNAME/DOMAIN.
     * This is specified by the RFC to be a list of uris separated by spaces that the client will be allowed to access.
     * An RFC in draft stage is proposing the removal of this functionality, it does not seem to be in widespread use.
     *
     * @param string $domain The list of uris separated by spaces that the client will be able to access upon successful authentication.
     *
     * @return void
     */
    private function setDomain($value)
    {
        $this->domain = $value;
    }

    /**
     * Sets the Entity Body of the Request for use with qop=auth-int
     *
     * @param string $entityBody The body of the entity (The unencoded request minus the headers).
     *
     * @return void
     */
    private function setEntityBody($entityBody = null)
    {
        $this->entityBody = $entityBody;
    }

    /**
     * Sets the HTTP method being used for the request
     *
     * @param string $method The HTTP method
     *
     * @throws \InvalidArgumentException If $method is set to anything other than GET,POST,PUT,DELETE or HEAD.
     *
     * @return void
     */
    private function setMethod($method = null)
    {
        if($method == 'GET') {
            $this->method = 'GET';
            return;
        }
        if($method == 'POST') {
            $this->method = 'POST';
            return;
        }
        if($method == 'PUT') {
            $this->method = 'PUT';
            return;
        }
        if($method == 'DELETE') {
            $this->method = 'DELETE';
            return;
        }
        if($method == 'HEAD') {
            $this->method = 'HEAD';
            return;
        }
        throw new \InvalidArgumentException('DigestAuthClient: Only GET,POST,PUT,DELETE,HEAD HTTP methods are currently supported.');
    }

    /**
     * Sets the value of nonce
     *
     * @param string $opaque The server nonce value
     *
     * @return void
     */
    private function setNonce($nonce = null)
    {
        $this->nonce = $nonce;
    }

    /**
     * Sets the value of opaque
     *
     * @param string $opaque The opaque value
     *
     * @return void
     */
    private function setOpaque($opaque)
    {
        $this->opaque = $opaque;
    }

    /**
     * Sets the acceptable value(s) for the quality of protection used by the server. Supported values are auth and auth-int.
     * TODO: This method should give precedence to using qop=auth-int first as this offers integrity protection.
     *
     * @param array $qop An array with the values of qop that the server has specified it will accept.
     *
     * @throws \InvalidArgumentException If $qop contains any values other than auth-int or auth.
     *
     * @return void
     */
    private function setQOP(array $qop = array())
    {
        $this->qop = array();
        foreach($qop as $protection) {
            $protection = trim($protection);
            if($protection == 'auth-int') {
                $this->qop[] = 'auth-int';
            } elseif($protection == 'auth') {
                $this->qop[] = 'auth';
            } else {
                throw new \InvalidArgumentException('DigestAuthClient: Only auth-int and auth are supported Quality of Protection mechanisms.');
            }
        }
    }

    /**
     * Sets the value of uri
     *
     * @param string $uri The uri
     *
     * @return void
     */
    private function setUri($uri = null)
    {
        $this->uri = $uri;
    }

    /**
     * If a string contains quotation marks at either end this function will strip them. Otherwise it will remain unchanged.
     *
     * @param string $str The string to be stripped of quotation marks.
     *
     * @return string Returns the original string without the quotation marks at either end.
     */
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