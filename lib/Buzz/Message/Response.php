<?php

namespace Buzz\Message;

class Response extends AbstractMessage
{
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    private $protocolVersion;
    private $statusCode;
    private $reasonPhrase;

    /**
     * Returns the protocol version of the current response.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        if (null === $this->protocolVersion) {
            $this->parseStatusLine();
        }

        return $this->protocolVersion ?: null;
    }

    /**
     * Returns the status code of the current response.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        if (null === $this->statusCode) {
            $this->parseStatusLine();
        }

        return $this->statusCode ?: null;
    }

    /**
     * Returns the reason phrase for the current response.
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        if (null === $this->reasonPhrase) {
            $this->parseStatusLine();
        }

        return $this->reasonPhrase ?: null;
    }

    public function setHeaders(array $headers)
    {
        parent::setHeaders($headers);

        $this->resetStatusLine();
    }

    public function addHeader($header)
    {
        parent::addHeader($header);

        $this->resetStatusLine();
    }

    public function addHeaders(array $headers)
    {
        parent::addHeaders($headers);

        $this->resetStatusLine();
    }

    /**
     * Is response invalid?
     *
     * @return boolean
     */
    public function isInvalid()
    {
        return $this->getStatusCode() < static::HTTP_CONTINUE || $this->getStatusCode() >= 600;
    }

    /**
     * Is response informative?
     *
     * @return boolean
     */
    public function isInformational()
    {
        return $this->getStatusCode() >= static::HTTP_CONTINUE && $this->getStatusCode() < static::HTTP_OK;
    }

    /**
     * Is response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->getStatusCode() >= static::HTTP_OK && $this->getStatusCode() < static::HTTP_MULTIPLE_CHOICES;
    }

    /**
     * Is the response a redirect?
     *
     * @return boolean
     */
    public function isRedirection()
    {
        return
            $this->getStatusCode() >= static::HTTP_MULTIPLE_CHOICES
            && $this->getStatusCode() < static::HTTP_BAD_REQUEST;
    }

    /**
     * Is there a client error?
     *
     * @return boolean
     */
    public function isClientError()
    {
        return
            $this->getStatusCode() >= static::HTTP_BAD_REQUEST
            && $this->getStatusCode() < static::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Was there a server side error?
     *
     * @return boolean
     */
    public function isServerError()
    {
        return $this->getStatusCode() >= static::HTTP_INTERNAL_SERVER_ERROR && $this->getStatusCode() < 600;
    }

    /**
     * Is the response OK?
     *
     * @return boolean
     */
    public function isOk()
    {
        return static::HTTP_OK === $this->getStatusCode();
    }

    /**
     * Is the response forbidden?
     *
     * @return boolean
     */
    public function isForbidden()
    {
        return static::HTTP_FORBIDDEN === $this->getStatusCode();
    }

    /**
     * Is the response a not found error?
     *
     * @return boolean
     */
    public function isNotFound()
    {
        return static::HTTP_NOT_FOUND === $this->getStatusCode();
    }

    /**
     * Is the response empty?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return in_array(
            $this->getStatusCode(),
            [static::HTTP_CREATED, static::HTTP_NO_CONTENT, static::HTTP_NOT_MODIFIED]
        );
    }

    // private

    private function parseStatusLine()
    {
        $headers = $this->getHeaders();

        if (isset($headers[0]) && 2 <= count($parts = explode(' ', $headers[0], 3))) {
            $this->protocolVersion = (string) substr($parts[0], 5);
            $this->statusCode = (integer) $parts[1];
            $this->reasonPhrase = isset($parts[2]) ? $parts[2] : '';
        } else {
            $this->protocolVersion = $this->statusCode = $this->reasonPhrase = false;
        }
    }

    private function resetStatusLine()
    {
        $this->protocolVersion = $this->statusCode = $this->reasonPhrase = null;
    }
}
