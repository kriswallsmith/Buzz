[<-- Index](/doc/Readme.md)

# Clients

Clients are the low-level objects to send HTTP requests. All clients are minimalistic both in
features and flexibility. You may give the client some default configuration and some additional
configuration each time you send a request.

There are 3 clients: `FileGetContents`, `Curl` and `MultiCurl`.

```php
use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;

$request = new Request('GET', 'https://example.com');

$client = new FileGetContents(new Psr17Factory(), ['allow_redirects' => true]);
$response = $client->sendRequest($request, ['timeout' => 4]);
```

## Configuration

Not all configuration works will all clients. If there is any client specific configuration it
will be noted below.

#### allow_redirects

Type: boolean<br>
Default: `false`

Should the client follow HTTP redirects or not.

#### callback

Type: callable<br>
Default: `function() {}`<br>
*Only for MultiCurl*

A callback function that is called after a request has been sent.

```php
use Nyholm\Psr7\Request;

$callback = function(RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) {
    // Process the response
};

$request = new Request('GET', 'https://example.com');
$client->sendAsyncRequest($request, array('callback' => $callback));
```

#### curl

Type: array<br>
Default: `[]`<br>
*Only for Curl and MultiCurl*

An array with Curl options.

```php
use Nyholm\Psr7\Request;

$request = new Request('GET', 'https://example.com');
$client->sendAsyncRequest($request, array('curl' => [
    CURLOPT_FAILONERROR => false,
]));
```

#### expose_curl_info

Type: boolean<br>
Default: `false`<br>
*Only for Curl and MultiCurl*

If set to `true` the response header `__curl_info` will contain a json_encoded
serialization of the curl metadata information about the response.

```php
use Nyholm\Psr7\Request;

$request = new Request('GET', 'https://example.com');
$response = $client->sendRequest($request, [
    'expose_curl_info' => true,
]);

$curlInfo = json_decode($response->getHeader('__curl_info')[0], true);
```

#### max_redirects

Type: integer<br>
Default: `5`

The maximum number of redirects to follow. Note that this will have no effect unless you set
`'allow_redirects' => true`.

#### proxy

Type: string<br>
Default: `null`

A proxy server to use when sending requests.

#### push_function_callback

Type: callable, null<br>
Default: `null`<br>
*Only for MultiCurl*

A callable for `CURLMOPT_PUSHFUNCTION`. See [PHP docs](https://php.net/manual/en/function.curl-multi-setopt.php).

Since MultiCurl supports adding multiple requests, all Push Functions callbacks are
chained together. If one of them returns `CURL_PUSH_DENY`, then the request will be denied. 

```php
$options['push_function_callback'] = function ($parent, $pushed, $headers) {
    if (strpos($header, ':path:') === 0) {
        $path = substr($header, 6);
        if ($path === '/foo/bar') {
            return CURL_PUSH_DENY;
        }

        return CURL_PUSH_OK;
};
```

#### timeout

Type: integer<br>
Default: `0` (no limit)

The time to wait before interrupt the request.

#### use_pushed_response

Type: boolean<br>
Default: `true`<br>
*Only for MultiCurl*

If true, we can used responses pushed to us by HTTP/2.0 server push. 

#### verify

Type: boolean<br>
Default: `true`

If SSL protocols should verified.

---

Continue reading about [Middleware](/doc/middleware.md).
