# Clients

Clients are the low-level objects to send HTTP requests. All clients are minimalistic both in 
features and flexibility. You may give the client some default configuration and some additional 
configuration each time you send a request. 

There are 3 clients: `FileGetContents`, `Curl` and `MultiCurl`. 

```php
$request = new PSR7Request('GET', 'https://example.com');

$client = new Buzz\Client\FileGetContents(['follow_redirects'=>true]);
$response = $client->send($request, ['timeout' => 4]);
```

## Configuration

Not all configuration works will all clients. If there is any client specific configuration it 
will be noted below. 


#### Callback

Type: callable<br>
Default: `function() {}`<br>
*Only for MultiCurl*

A callback function that is called after a request has been sent. 

```php
$callback = function(RequestInterface $request, ResponseInterface $response = null, ClientException $exception = null) {
    $calls[] = func_get_args();
};
$request = new PSR7Request('GET', 'https://example.com');
$client->sendAsyncRequest($request, array('callback' => $callback));
```

#### Curl

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

#### follow_redirects

Type: boolean<br>
Default: `false`

Should the client follow HTTP redirects or not. 

#### max_redirects

Type: integer<br>
Default: `5`

The maximum number of redirects to follow. 

#### proxy

Type: string<br>
Default: `null`

A proxy server to use when sending requests. 

#### timeout

Type: integer<br>
Default: `30`

The time to wait before interrupt the request. 

#### verify_host

Type: boolean<br>
Default: `true`

#### verify_peer

Type: boolean<br>
Default: `true`
