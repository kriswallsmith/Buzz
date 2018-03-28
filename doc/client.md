[<-- Index](/doc/index.md)

# Clients

Clients are the low-level objects to send HTTP requests. All clients are minimalistic both in 
features and flexibility. You may give the client some default configuration and some additional 
configuration each time you send a request. 

There are 3 clients: `FileGetContents`, `Curl` and `MultiCurl`. 

```php
$request = new PSR7Request('GET', 'https://example.com');

$client = new Buzz\Client\FileGetContents(['allow_redirects'=>true]);
$response = $client->send($request, ['timeout' => 4]);
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
$callback = function(RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) {
    // Process the response
};
$request = new PSR7Request('GET', 'https://example.com');
$client->sendAsyncRequest($request, array('callback' => $callback));
```

#### curl

Type: array<br>
Default: `[]`<br>
*Only for Curl and MultiCurl*

An array with Curl options. 

```php
$request = new PSR7Request('GET', 'https://example.com');
$client->sendAsyncRequest($request, array('curl' => [
    CURLOPT_FAILONERROR => false,
]));
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

#### timeout

Type: integer<br>
Default: `30`

The time to wait before interrupt the request. 

#### verify

Type: boolean<br>
Default: `true`

If SSL protocols should verified. 

---

Continue reading about [Middlewares](/doc/middlewares.md).
