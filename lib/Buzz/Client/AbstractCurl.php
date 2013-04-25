<?php

namespace Buzz\Client;

use Buzz\Message\Form\FormRequestInterface;
use Buzz\Message\Form\FormUploadInterface;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Buzz\Exception\ClientException;

/**
 * Base client class with helpers for working with cURL.
 */
abstract class AbstractCurl extends AbstractClient
{
    static $curl_error_codes = array(
        CURLE_UNSUPPORTED_PROTOCOL => 'UNSUPPORTED_PROTOCOL',
        CURLE_FAILED_INIT => 'FAILED_INIT',
        CURLE_URL_MALFORMAT => 'URL_MALFORMAT',
        CURLE_URL_MALFORMAT_USER => 'URL_MALFORMAT_USER',
        CURLE_COULDNT_RESOLVE_PROXY => 'COULDNT_RESOLVE_PROXY',
        CURLE_COULDNT_RESOLVE_HOST => 'COULDNT_RESOLVE_HOST',
        CURLE_COULDNT_CONNECT => 'COULDNT_CONNECT',
        CURLE_FTP_WEIRD_SERVER_REPLY => 'FTP_WEIRD_SERVER_REPLY',
        CURLE_FTP_ACCESS_DENIED => 'FTP_ACCESS_DENIED',
        CURLE_FTP_USER_PASSWORD_INCORRECT => 'FTP_USER_PASSWORD_INCORRECT',
        CURLE_FTP_WEIRD_PASS_REPLY => 'FTP_WEIRD_PASS_REPLY',
        CURLE_FTP_WEIRD_USER_REPLY => 'FTP_WEIRD_USER_REPLY',
        CURLE_FTP_WEIRD_PASV_REPLY => 'FTP_WEIRD_PASV_REPLY',
        CURLE_FTP_WEIRD_227_FORMAT => 'FTP_WEIRD_227_FORMAT',
        CURLE_FTP_CANT_GET_HOST => 'FTP_CANT_GET_HOST',
        CURLE_FTP_CANT_RECONNECT => 'FTP_CANT_RECONNECT',
        CURLE_FTP_COULDNT_SET_BINARY => 'FTP_COULDNT_SET_BINARY',
        CURLE_PARTIAL_FILE => 'PARTIAL_FILE',
        CURLE_FTP_COULDNT_RETR_FILE => 'FTP_COULDNT_RETR_FILE',
        CURLE_FTP_WRITE_ERROR => 'FTP_WRITE_ERROR',
        CURLE_FTP_QUOTE_ERROR => 'FTP_QUOTE_ERROR',
        CURLE_HTTP_NOT_FOUND => 'HTTP_NOT_FOUND',
        CURLE_WRITE_ERROR => 'WRITE_ERROR',
        CURLE_MALFORMAT_USER => 'MALFORMAT_USER',
        CURLE_FTP_COULDNT_STOR_FILE => 'FTP_COULDNT_STOR_FILE',
        CURLE_READ_ERROR => 'READ_ERROR',
        CURLE_OUT_OF_MEMORY => 'OUT_OF_MEMORY',
        CURLE_OPERATION_TIMEOUTED => 'OPERATION_TIMEOUTED',
        CURLE_FTP_COULDNT_SET_ASCII => 'FTP_COULDNT_SET_ASCII',
        CURLE_FTP_PORT_FAILED => 'FTP_PORT_FAILED',
        CURLE_FTP_COULDNT_USE_REST => 'FTP_COULDNT_USE_REST',
        CURLE_FTP_COULDNT_GET_SIZE => 'FTP_COULDNT_GET_SIZE',
        CURLE_HTTP_RANGE_ERROR => 'HTTP_RANGE_ERROR',
        CURLE_HTTP_POST_ERROR => 'HTTP_POST_ERROR',
        CURLE_SSL_CONNECT_ERROR => 'SSL_CONNECT_ERROR',
        CURLE_FTP_BAD_DOWNLOAD_RESUME => 'FTP_BAD_DOWNLOAD_RESUME',
        CURLE_FILE_COULDNT_READ_FILE => 'FILE_COULDNT_READ_FILE',
        CURLE_LDAP_CANNOT_BIND => 'LDAP_CANNOT_BIND',
        CURLE_LDAP_SEARCH_FAILED => 'LDAP_SEARCH_FAILED',
        CURLE_LIBRARY_NOT_FOUND => 'LIBRARY_NOT_FOUND',
        CURLE_FUNCTION_NOT_FOUND => 'FUNCTION_NOT_FOUND',
        CURLE_ABORTED_BY_CALLBACK => 'ABORTED_BY_CALLBACK',
        CURLE_BAD_FUNCTION_ARGUMENT => 'BAD_FUNCTION_ARGUMENT',
        CURLE_BAD_CALLING_ORDER => 'BAD_CALLING_ORDER',
        CURLE_HTTP_PORT_FAILED => 'HTTP_PORT_FAILED',
        CURLE_BAD_PASSWORD_ENTERED => 'BAD_PASSWORD_ENTERED',
        CURLE_TOO_MANY_REDIRECTS => 'TOO_MANY_REDIRECTS',
        CURLE_UNKNOWN_TELNET_OPTION => 'UNKNOWN_TELNET_OPTION',
        CURLE_TELNET_OPTION_SYNTAX => 'TELNET_OPTION_SYNTAX',
        CURLE_OBSOLETE => 'OBSOLETE',
        CURLE_SSL_PEER_CERTIFICATE => 'SSL_PEER_CERTIFICATE',
        CURLE_GOT_NOTHING => 'GOT_NOTHING',
        CURLE_SSL_ENGINE_NOTFOUND => 'SSL_ENGINE_NOTFOUND',
        CURLE_SSL_ENGINE_SETFAILED => 'SSL_ENGINE_SETFAILED',
        CURLE_SEND_ERROR => 'SEND_ERROR',
        CURLE_RECV_ERROR => 'RECV_ERROR',
        CURLE_SHARE_IN_USE => 'SHARE_IN_USE',
        CURLE_SSL_CERTPROBLEM => 'SSL_CERTPROBLEM',
        CURLE_SSL_CIPHER => 'SSL_CIPHER',
        CURLE_SSL_CACERT => 'SSL_CACERT',
        CURLE_BAD_CONTENT_ENCODING => 'BAD_CONTENT_ENCODING',
        CURLE_LDAP_INVALID_URL => 'LDAP_INVALID_URL',
        CURLE_FILESIZE_EXCEEDED => 'FILESIZE_EXCEEDED',
        CURLE_FTP_SSL_FAILED => 'FTP_SSL_FAILED'
    );

    protected $options = array();

    public function __construct()
    {
        if (defined('CURLOPT_PROTOCOLS')) {
            $this->options = array(
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            );
        }
    }

    /**
     * Creates a new cURL resource.
     *
     * @see curl_init()
     *
     * @return resource A new cURL resource
     */
    protected static function createCurlHandle()
    {
        if (false === $curl = curl_init()) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    /**
     * Populates a response object.
     *
     * @param resource         $curl     A cURL resource
     * @param string           $raw      The raw response string
     * @param MessageInterface $response The response object
     */
    protected static function populateResponse($curl, $raw, MessageInterface $response)
    {
        $pos = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        $response->setHeaders(static::getLastHeaders(rtrim(substr($raw, 0, $pos))));
        $response->setContent(substr($raw, $pos));
    }

    /**
     * Sets options on a cURL resource based on a request.
     */
    private static function setOptionsFromRequest($curl, RequestInterface $request)
    {
        $options = array(
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL           => $request->getHost().$request->getResource(),
            CURLOPT_HTTPHEADER    => $request->getHeaders(),
        );

        switch ($request->getMethod()) {
            case RequestInterface::METHOD_HEAD:
                $options[CURLOPT_NOBODY] = true;
                break;

            case RequestInterface::METHOD_GET:
                $options[CURLOPT_HTTPGET] = true;
                break;

            case RequestInterface::METHOD_POST:
            case RequestInterface::METHOD_PUT:
            case RequestInterface::METHOD_DELETE:
            case RequestInterface::METHOD_PATCH:
                $options[CURLOPT_POSTFIELDS] = $fields = static::getPostFields($request);

                // remove the content-type header
                if (is_array($fields)) {
                    $options[CURLOPT_HTTPHEADER] = array_filter($options[CURLOPT_HTTPHEADER], function($header) {
                        return 0 !== stripos($header, 'Content-Type: ');
                    });
                }

                break;
        }

        curl_setopt_array($curl, $options);
    }

    /**
     * Returns a value for the CURLOPT_POSTFIELDS option.
     *
     * @return string|array A post fields value
     */
    private static function getPostFields(RequestInterface $request)
    {
        if (!$request instanceof FormRequestInterface) {
            return $request->getContent();
        }

        $fields = $request->getFields();
        $multipart = false;

        foreach ($fields as $name => $value) {
            if ($value instanceof FormUploadInterface) {
                $multipart = true;

                if ($file = $value->getFile()) {
                    // replace value with upload string
                    $fields[$name] = '@'.$file;

                    if ($contentType = $value->getContentType()) {
                        $fields[$name] .= ';type='.$contentType;
                    }
                } else {
                    return $request->getContent();
                }
            }
        }

        return $multipart ? $fields : http_build_query($fields);
    }

    /**
     * A helper for getting the last set of headers.
     *
     * @param string $raw A string of many header chunks
     *
     * @return array An array of header lines
     */
    private static function getLastHeaders($raw)
    {
        $headers = array();
        foreach (preg_split('/(\\r?\\n)/', $raw) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = array();
            }
        }

        return $headers;
    }

    /**
     * Stashes a cURL option to be set on send, when the resource is created.
     *
     * If the supplied value it set to null the option will be removed.
     *
     * @param integer $option The option
     * @param mixed   $value  The value
     *
     * @see curl_setopt()
     */
    public function setOption($option, $value)
    {
        if (null === $value) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }
    }

    /**
     * Prepares a cURL resource to send a request.
     */
    protected function prepare($curl, RequestInterface $request, array $options = array())
    {
        static::setOptionsFromRequest($curl, $request);

        // apply settings from client
        if ($this->getTimeout() < 1) {
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->getTimeout() * 1000);
        } else {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->getTimeout());
        }

        if ($this->proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
        }

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->getMaxRedirects());
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->getMaxRedirects());
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->getIgnoreErrors());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->getVerifyPeer());

        // apply additional options
        curl_setopt_array($curl, $options + $this->options);
    }
}
