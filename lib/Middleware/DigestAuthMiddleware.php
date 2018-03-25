<?php

declare(strict_types=1);

namespace Buzz\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DigestAuthMiddleware implements MiddlewareInterface
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

    /** @var string[] Quality of Protection */
    private $qop = [];

    /**
     * QOP options: Only one of the following can be set at any time. setOptions will throw an exception otherwise.
     * OPTION_QOP_AUTH_INT       - Always use auth-int   (if available)
     * OPTION_QOP_AUTH           - Always use auth       (even if auth-int available).
     */
    const OPTION_QOP_AUTH_INT = 1;

    const OPTION_QOP_AUTH = 2;

    /**
     * Ignore server request to downgrade authentication from Digest to Basic.
     * Breaks RFC compatibility, but ensures passwords are never sent using base64 which is trivial for an attacker to decode.
     */
    const OPTION_IGNORE_DOWNGRADE_REQUEST = 4;

    /**
     * Discard Client Nonce on each request.
     */
    const OPTION_DISCARD_CLIENT_NONCE = 8;

    private $options;

    /**
     * Set OPTION_QOP_BEST_AVAILABLE and OPTION_DISCARD_CLIENT_NONCE by default.
     */
    public function __construct(string $username = null, string $password = null, string $realm = null)
    {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setRealm($realm);
        $this->setOptions(self::OPTION_QOP_AUTH_INT & self::OPTION_DISCARD_CLIENT_NONCE);
    }

    /**
     * Populates uri, method and entityBody used to generate the Authentication header using the specified request object.
     * Appends the Authentication header if it is present and has been able to be calculated.
     */
    public function handleRequest(RequestInterface $request, callable $next)
    {
        $this->setUri($request->getUri()->getPath());
        $this->setMethod(strtoupper($request->getMethod()));
        $this->setEntityBody($request->getBody()->__toString());

        if (null !== $header = $this->getHeader()) {
            $request = $request->withHeader('Authorization', $header);
        }

        return $next($request);
    }

    /**
     * Passes the returned server headers to parseServerHeaders() to check if any authentication variables need to be set.
     * Inteprets the returned status code and attempts authentication if status is 401 (Authentication Required) by resending
     * the last request with an Authentication header.
     */
    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->parseServerHeaders($response);

        return $next($request, $response);
    }

    /**
     * Sets the password to be used to authenticate the client.
     *
     * @param string $password The password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * Sets the realm to be used to authenticate the client.
     *
     * @param string $realm The realm
     */
    public function setRealm(?string $realm): void
    {
        $this->realm = $realm;
    }

    /**
     * Sets the username to be used to authenticate the client.
     *
     * @param string $username The username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * Sets the options to be used by this class.
     *
     * @param mixed $options a bitmask of the constants defined in this class
     */
    public function setOptions($options): void
    {
        if ($options & self::OPTION_QOP_AUTH_INT) {
            if ($options & self::OPTION_QOP_AUTH) {
                throw new \InvalidArgumentException('DigestAuthMiddleware: Only one value of OPTION_QOP_AUTH_INT or OPTION_QOP_AUTH may be set.');
            }
            $this->options = $this->options | self::OPTION_QOP_AUTH_INT;
        } elseif ($options & self::OPTION_QOP_AUTH) {
            $this->options = $this->options | self::OPTION_QOP_AUTH;
        }

        if ($options & self::OPTION_IGNORE_DOWNGRADE_REQUEST) {
            $this->options = $this->options | self::OPTION_IGNORE_DOWNGRADE_REQUEST;
        }

        if ($options & self::OPTION_DISCARD_CLIENT_NONCE) {
            $this->options = $this->options | self::OPTION_DISCARD_CLIENT_NONCE;
        }
    }

    /**
     * Discards the Client Nonce forcing the generation of a new Client Nonce on the next request.
     */
    private function discardClientNonce(): void
    {
        $this->clientNonce = null;
    }

    /**
     * Returns the hashing algorithm to be used to generate the digest value. Currently only returns MD5.
     *
     * @return string the hashing algorithm to be used
     */
    private function getAlgorithm(): ?string
    {
        if (null == $this->algorithm) {
            $this->algorithm = 'MD5';
        }

        return $this->algorithm;
    }

    /**
     * Returns the authentication method requested by the server.
     * If OPTION_IGNORE_DOWNGRADE_REQUEST is set this will always return "Digest".
     *
     * @return string returns either "Digest" or "Basic"
     */
    private function getAuthenticationMethod(): ?string
    {
        if ($this->options & self::OPTION_IGNORE_DOWNGRADE_REQUEST) {
            return 'Digest';
        }

        return $this->authenticationMethod;
    }

    /**
     * Returns either the current value of clientNonce or generates a new value if clientNonce is null.
     * Also increments nonceCount.
     *
     * @return string Returns either the current value of clientNonce the newly generated clientNonce;
     */
    private function getClientNonce(): ?string
    {
        if (null == $this->clientNonce) {
            $this->clientNonce = uniqid();

            if (null == $this->nonceCount) {
                // If nonceCount is not set then set it to 00000001.
                $this->nonceCount = '00000001';
            } else {
                // If it is set then increment it.
                ++$this->nonceCount;
                // Ensure nonceCount is zero-padded at the start of the string to a length of 8
                while (strlen($this->nonceCount) < 8) {
                    $this->nonceCount = '0'.$this->nonceCount;
                }
            }
        }

        return $this->clientNonce;
    }

    /**
     * Returns a space separated list of uris that the server nonce can be used to generate an authentication response against.
     *
     * @return string space separated list of uris
     */
    private function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Returns the entity body of the current request.
     * The entity body is the request before it has been encoded with the content-encoding and minus the request headers.
     *
     * @return string the full entity-body
     */
    private function getEntityBody(): ?string
    {
        return (string) $this->entityBody;
    }

    /**
     * Calculates the value of HA1 according to RFC 2617 and RFC 2069.
     *
     * @return string|null The value of HA1
     */
    private function getHA1(): ?string
    {
        $username = $this->getUsername();
        $password = $this->getPassword();
        $realm = $this->getRealm();

        if (($username) && ($password) && ($realm)) {
            $algorithm = $this->getAlgorithm();

            if ('MD5' === $algorithm) {
                $A1 = "{$username}:{$realm}:{$password}";

                return $this->hash($A1);
            } elseif ('MD5-sess' === $algorithm) {
                $nonce = $this->getNonce();
                $cnonce = $this->getClientNonce();
                if (($nonce) && ($cnonce)) {
                    $A1 = $this->hash("{$username}:{$realm}:{$password}").":{$nonce}:{$cnonce}";

                    return $this->hash($A1);
                }
            }
        }

        return null;
    }

    /**
     * Calculates the value of HA2 according to RFC 2617 and RFC 2069.
     *
     * @return string The value of HA2
     */
    private function getHA2(): ?string
    {
        $method = $this->getMethod();
        $uri = $this->getUri();

        if (($method) && ($uri)) {
            $qop = $this->getQOP();

            if (null === $qop || 'auth' === $qop) {
                $A2 = "{$method}:{$uri}";
            } elseif ('auth-int' === $qop) {
                $entityBody = (string) $this->getEntityBody();
                $A2 = "{$method}:{$uri}:".(string) $this->hash($entityBody);
            } else {
                return null;
            }

            $HA2 = $this->hash($A2);

            return $HA2;
        }

        return null;
    }

    /**
     * Returns the full Authentication header for use in authenticating the client with either Digest or Basic authentication.
     *
     * @return string the Authentication header to be sent to the server
     */
    private function getHeader(): ?string
    {
        if ('Digest' == $this->getAuthenticationMethod()) {
            $username = $this->getUsername();
            $realm = $this->getRealm();
            $nonce = $this->getNonce();
            $response = $this->getResponse();
            if (($username) && ($realm) && ($nonce) && ($response)) {
                $uri = $this->getUri();
                $opaque = $this->getOpaque();
                $qop = $this->getQOP();

                $header = 'Digest';
                $header .= ' username="'.$username.'",';
                $header .= ' realm="'.$realm.'",';
                $header .= ' nonce="'.$nonce.'",';
                $header .= ' response="'.$response.'",';

                if ($uri) {
                    $header .= ' uri="'.$uri.'",';
                }
                if ($opaque) {
                    $header .= ' opaque="'.$opaque.'",';
                }

                if ($qop) {
                    $header .= ' qop='.$qop.',';

                    $cnonce = $this->getClientNonce();
                    $nc = $this->getNonceCount();

                    if ($cnonce) {
                        $header .= ' nc='.$nc.',';
                    }
                    if ($cnonce) {
                        $header .= ' cnonce="'.$cnonce.'",';
                    }
                }

                // Remove the last comma from the header
                $header = substr($header, 0, strlen($header) - 1);
                // Discard the Client Nonce if OPTION_DISCARD_CLIENT_NONCE is set.
                if ($this->options & self::OPTION_DISCARD_CLIENT_NONCE) {
                    $this->discardClientNonce();
                }

                return $header;
            }
        }
        if ('Basic' == $this->getAuthenticationMethod()) {
            $username = $this->getUsername();
            $password = $this->getPassword();
            if (($username) && ($password)) {
                $header = 'Basic '.base64_encode("{$username}:{$password}");

                return $header;
            }
        }

        return null;
    }

    /**
     * Returns the HTTP method used in the current request.
     *
     * @return string one of GET,POST,PUT,DELETE or HEAD
     */
    private function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Returns the value of nonce we have received in the server headers.
     *
     * @return string the value of the server nonce
     */
    private function getNonce(): ?string
    {
        return $this->nonce;
    }

    /**
     * Returns the current nonce counter for the client nonce.
     *
     * @return string an eight digit zero-padded string which reflects the number of times the clientNonce has been generated
     */
    private function getNonceCount(): ?string
    {
        return $this->nonceCount;
    }

    /**
     * Returns the opaque value that was sent to us from the server.
     *
     * @return string the value of opaque
     */
    private function getOpaque(): ?string
    {
        return $this->opaque;
    }

    /**
     * Returns the plaintext password for the client.
     *
     * @return string the value of password
     */
    private function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Returns either the realm specified by the client, or the realm specified by the server.
     * If the server set the value of realm then anything set by our client is overwritten.
     *
     * @return string the value of realm
     */
    private function getRealm(): ?string
    {
        return $this->realm;
    }

    /**
     * Calculates the value of response according to RFC 2617 and RFC 2069.
     *
     * @return string The value of response
     */
    private function getResponse(): ?string
    {
        $HA1 = $this->getHA1();
        $nonce = $this->getNonce();
        $HA2 = $this->getHA2();

        if (null !== $HA1 && ($nonce) && null !== $HA2) {
            $qop = $this->getQOP();

            if (empty($qop)) {
                $response = $this->hash("{$HA1}:{$nonce}:{$HA2}");

                return $response;
            }

            $cnonce = $this->getClientNonce();
            $nc = $this->getNonceCount();
            if (($cnonce) && ($nc)) {
                $response = $this->hash("{$HA1}:{$nonce}:{$nc}:{$cnonce}:{$qop}:{$HA2}");

                return $response;
            }
        }

        return null;
    }

    /**
     * Returns the Quality of Protection to be used when authenticating with the server.
     *
     * @return string this will either be auth-int or auth
     */
    private function getQOP(): ?string
    {
        // Has the server specified any options for Quality of Protection
        if (count($this->qop) > 0) {
            if ($this->options & self::OPTION_QOP_AUTH_INT) {
                if (in_array('auth-int', $this->qop)) {
                    return 'auth-int';
                }
                if (in_array('auth', $this->qop)) {
                    return 'auth';
                }
            }
            if ($this->options & self::OPTION_QOP_AUTH) {
                if (in_array('auth', $this->qop)) {
                    return 'auth';
                }
                if (in_array('auth-int', $this->qop)) {
                    return 'auth-int';
                }
            }
        }
        // Server has not specified any value for Quality of Protection so return null
        return null;
    }

    /**
     * Returns the username set by the client to authenticate with the server.
     *
     * @return string The value of username
     */
    private function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Returns the uri that we are requesting access to.
     *
     * @return string The value of uri
     */
    private function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * Calculates the hash for a given value using the algorithm specified by the server.
     *
     * @param string $value The value to be hashed
     *
     * @return string the hashed value
     */
    private function hash($value): ?string
    {
        $algorithm = $this->getAlgorithm();
        if (('MD5' == $algorithm) || ('MD5-sess' == $algorithm)) {
            return hash('md5', $value);
        }

        return null;
    }

    /**
     * Parses the Authentication-Info header received from the server and calls the relevant setter method on each variable received.
     *
     * @param string $authenticationInfo the full Authentication-Info header
     */
    private function parseAuthenticationInfoHeader(string $authenticationInfo): void
    {
        $nameValuePairs = $this->parseNameValuePairs($authenticationInfo);
        foreach ($nameValuePairs as $name => $value) {
            switch ($name) {
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
     * @param string $nameValuePairs the string containing the name=value pairs
     *
     * @return array an array with the name used as the index and the values stored within
     */
    private function parseNameValuePairs(string $nameValuePairs): array
    {
        $parsedNameValuePairs = [];
        $nameValuePairs = explode(',', $nameValuePairs);
        foreach ($nameValuePairs as $nameValuePair) {
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
     * @param ResponseInterface $response
     */
    private function parseServerHeaders(ResponseInterface $response): void
    {
        // Check to see if the WWW-Authenticate header is present and if so set $authHeader
        if (!empty($header = $response->getHeaderLine('www-authenticate'))) {
            $this->parseWwwAuthenticateHeader($header);
        }

        // Check to see if the Authentication-Info header is present and if so set $authInfo
        if (!empty($header = $response->getHeaderLine('authentication-info'))) {
            $this->parseAuthenticationInfoHeader($header);
        }
    }

    /**
     * Parses the WWW-Authenticate header received from the server and calls the relevant setter method on each variable received.
     *
     * @param string $wwwAuthenticate the full WWW-Authenticate header
     */
    private function parseWwwAuthenticateHeader(string $wwwAuthenticate): void
    {
        if ('Digest ' == substr($wwwAuthenticate, 0, 7)) {
            $this->setAuthenticationMethod('Digest');
            // Remove "Digest " from start of header
            $wwwAuthenticate = substr($wwwAuthenticate, 7, strlen($wwwAuthenticate) - 7);

            $nameValuePairs = $this->parseNameValuePairs($wwwAuthenticate);

            foreach ($nameValuePairs as $name => $value) {
                switch ($name) {
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
        if ('Basic ' == substr($wwwAuthenticate, 0, 6)) {
            $this->setAuthenticationMethod('Basic');
            // Remove "Basic " from start of header
            $wwwAuthenticate = substr($wwwAuthenticate, 6, strlen($wwwAuthenticate) - 6);

            $nameValuePairs = $this->parseNameValuePairs($wwwAuthenticate);

            foreach ($nameValuePairs as $name => $value) {
                switch ($name) {
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
     * @param string $algorithm the algorithm the server has requested to use
     *
     * @throws \InvalidArgumentException if $algorithm is set to anything other than MD5 or MD5-sess
     */
    private function setAlgorithm(string $algorithm): void
    {
        if (('MD5' == $algorithm) || ('MD5-sess' == $algorithm)) {
            $this->algorithm = $algorithm;
        } else {
            throw new \InvalidArgumentException('DigestAuthMiddleware: Only MD5 and MD5-sess algorithms are currently supported.');
        }
    }

    /**
     * Sets authentication method to be used. Options are "Digest" and "Basic".
     * If the server and the client are unable to authenticate using Digest then the RFCs state that the server should attempt
     * to authenticate the client using Basic authentication. This ensures that we adhere to that behaviour.
     * This does however create the possibilty of a downgrade attack so it may be an idea to add a way of disabling this functionality
     * as Basic authentication is trivial to decrypt and exposes the username/password to a man-in-the-middle attack.
     *
     * @param string $authenticationMethod the authentication method requested by the server
     *
     * @throws \InvalidArgumentException If $authenticationMethod is set to anything other than Digest or Basic
     */
    private function setAuthenticationMethod(string $authenticationMethod): void
    {
        if ('Digest' === $authenticationMethod || 'Basic' === $authenticationMethod) {
            $this->authenticationMethod = $authenticationMethod;
        } else {
            throw new \InvalidArgumentException('DigestAuthMiddleware: Only Digest and Basic authentication methods are currently supported.');
        }
    }

    /**
     * Sets the domain to be authenticated against. THIS IS NOT TO BE CONFUSED WITH THE HOSTNAME/DOMAIN.
     * This is specified by the RFC to be a list of uris separated by spaces that the client will be allowed to access.
     * An RFC in draft stage is proposing the removal of this functionality, it does not seem to be in widespread use.
     *
     * @param string $domain the list of uris separated by spaces that the client will be able to access upon successful authentication
     */
    private function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * Sets the Entity Body of the Request for use with qop=auth-int.
     *
     * @param string $entityBody the body of the entity (The unencoded request minus the headers)
     */
    private function setEntityBody(string $entityBody = null): void
    {
        $this->entityBody = $entityBody;
    }

    /**
     * Sets the HTTP method being used for the request.
     *
     * @param string $method The HTTP method
     *
     * @throws \InvalidArgumentException if $method is set to anything other than GET,POST,PUT,DELETE or HEAD
     */
    private function setMethod(string $method = null): void
    {
        if ('GET' == $method) {
            $this->method = 'GET';

            return;
        }
        if ('POST' == $method) {
            $this->method = 'POST';

            return;
        }
        if ('PUT' == $method) {
            $this->method = 'PUT';

            return;
        }
        if ('DELETE' == $method) {
            $this->method = 'DELETE';

            return;
        }
        if ('HEAD' == $method) {
            $this->method = 'HEAD';

            return;
        }

        throw new \InvalidArgumentException('DigestAuthMiddleware: Only GET,POST,PUT,DELETE,HEAD HTTP methods are currently supported.');
    }

    /**
     * Sets the value of nonce.
     *
     * @param string $nonce The server nonce value
     */
    private function setNonce(string $nonce = null): void
    {
        $this->nonce = $nonce;
    }

    /**
     * Sets the value of opaque.
     *
     * @param string $opaque The opaque value
     */
    private function setOpaque(string $opaque): void
    {
        $this->opaque = $opaque;
    }

    /**
     * Sets the acceptable value(s) for the quality of protection used by the server. Supported values are auth and auth-int.
     * TODO: This method should give precedence to using qop=auth-int first as this offers integrity protection.
     *
     * @param array $qop an array with the values of qop that the server has specified it will accept
     *
     * @throws \InvalidArgumentException if $qop contains any values other than auth-int or auth
     */
    private function setQOP(array $qop = []): void
    {
        $this->qop = [];
        foreach ($qop as $protection) {
            $protection = trim($protection);
            if ('auth-int' == $protection) {
                $this->qop[] = 'auth-int';
            } elseif ('auth' == $protection) {
                $this->qop[] = 'auth';
            } else {
                throw new \InvalidArgumentException('DigestAuthMiddleware: Only auth-int and auth are supported Quality of Protection mechanisms.');
            }
        }
    }

    /**
     * Sets the value of uri.
     *
     * @param string $uri The uri
     */
    private function setUri(string $uri = null): void
    {
        $this->uri = $uri;
    }

    /**
     * If a string contains quotation marks at either end this function will strip them. Otherwise it will remain unchanged.
     *
     * @param string $str the string to be stripped of quotation marks
     *
     * @return string returns the original string without the quotation marks at either end
     */
    private function unquoteString(string $str = null): ?string
    {
        if ($str) {
            if ('"' == substr($str, 0, 1)) {
                $str = substr($str, 1, strlen($str) - 1);
            }
            if ('"' == substr($str, strlen($str) - 1, 1)) {
                $str = substr($str, 0, strlen($str) - 1);
            }
        }

        return $str;
    }
}
