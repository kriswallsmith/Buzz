# Change log

The change log shows what have been Added, Changed, Deprecated and Removed between versions. 

## 1.0.0-beta2

### Changed

- `MultiCurl` will never throw exception when handling messages asynchronously. 
All exceptions will be handled in the callback. 
- `MultiCurl::sendRequest()` will throw exception if one occur. 
- Make sure `MultiCurl::proceed()` is non-blocking.

## 1.0.0-beta1

### Added

- `ContentTypeMiddleware` that automatically detects content type of the body. 

### Changed

- It is now mandatory to pass a client to the `Browser`'s constructor.
- First argument of `Curl`, `MultiCurl` and `FileGetContent` clients should be the response factory.
- Using stable version of `psr/http-client`.

## 0.17.2

### Changed

- Added parameter for `ResponseFactory` to `AbstractClient` constructor.
- Added parameter for  `RequestFactory` to `Browser` constructor.

### Deprecated

- Not passing a RequestFactory to `Browser`.
- Not passing a ResponseFactory to the client's constructor.
- Not passing a BuzzClientInterface to the `Browser`'s constructor.


## 0.17.1

### Added

- Updated composer.json to show that we provide `php-http/client-implementation: 1.0`.

## 0.17.0

### Added

- The first argument to all client constructors is an array of options. 
- A way to configure default options for a client: `AbstractClient::configureOptions(OptionsResolver $resolver)`
- Added `ParameterBag` to store options. 
- Added `BatchClientInterface::sendAsyncRequest(RequestInterface $request, array $options = [])`.
- Added `BuzzClientInterface::sendRequest(RequestInterface $request, array $options = []): ResponseInterface`.
- Ported all Listeners to Middlewares. 
- Added options to configure the client as constructor argument and when you send a request. 

### Removed (BC breaks)

- Removed `Request` and `Response`
- Removed `AbstractStream`.
- Removed all listeners and `ListenerInterface`
- Removed `Curl::getInfo()`
- Client functions like `AbstractClient::setIgnoreErrors()`, `AbstractClient::getIgnoreErrors()`, `AbstractClient::setMaxRedirects()`, 
`AbstractClient::getMaxRedirects()`, `AbstractClient::setTimeout()`, `AbstractClient::getTimeout()`, 
`AbstractClient::setVerifyPeer()`, `AbstractClient::getVerifyPeer()`, `AbstractClient::getVerifyHost()`, 
`AbstractClient::setVerifyHost()`, `AbstractClient::setProxy()` and `AbstractClient::getProxy()`.

### Changed (BC breaks)

- Redirects are not followed by default
- No exceptions are thrown and no warnings are triggered on a invalid response. 
- We only handle PSR requests and responses. 
- Renamed `Browser::call($url, $method, $headers, $body)` to `Browser::request($method, $url, $headers, $body)`. 

## 0.16.1

### Added

- `BasicAuthMiddleware`, `BearerAuthMiddleware`, `ContentLengthMiddleware` and `LoggerMiddleware`. 
- `Browser::submitForm`
- Support for middleware chain when using `BatchClientInterface`
- `FormRequestBuilder` to help build a "form request".
- Added HTTP status code constants to `Response`.

### Changed

- Used Curl read function for large bodies.

### Deprecated

- Deprecated `Browser::send` in favor for `Browser::sendRequest`
- Deprecated `Browser::submit` in favor for `Browser::submitForm`

### Fixed

- Make sure `Browser` does not call deprecated functions. 

## 0.16.0

### Changed

* ClientInterface supports PSR7 requests and responses. 
* Removed type hint for second parameter for `AbstractCurl::prepare`.
* Removed type hint for second parameter for `RequestException::setRequest`.
* Removed type hint for first parameter for `AbstractStream::getStreamContextArray`.
* `RequestException` supports both PSR-7 requests and Buzz requests. 
* `Response::getProtocolVersion` will return a string and not a float. 
* Using PHPUnit namespaces.

### Added 

* Added Request and Response converters
* Added `Curl::sendRequest()`, `MultiCurl::sendRequest()` and `FileGetContents::sendRequest()` that
  supports sending PSR-7 requests. 
* Added `Browser::sendRequest()` that supports middlewares.  
* Added `MiddlewareInterface` and `Browser::addMiddleware()`.
* Added `HeaderConverter` to convert between PSR-7 styled headers and Buzz styled headers. 

### Deprecated

* `Curl::send()`, `MultiCurl::send()` and `FileGetContents::send()` in favor for `*::sendRequest()`. 
* `AbstractCurl::populateResponse()` was deprecated in favor of `AbstractCurl::createResponse()`.

### Removed

* Support for PHP 5.3.

## 0.15.2

### Added 

* A `.gitattributes` was added to exclude test files and metadata.

### Changed

* The reason phrase is allowed to be empty.  

## 0.15.1 

### Fixed

 * `MultiCurl` will throw exception when request fails. This makes `MultiCurl` to have the same behavior as `Curl` and 
   `FileGetContents`. (Liskov Substitution Principle)  

### Added

* Added `BearerAuthListener`

### Changed

 * We use PSR-4 instead of PSR-0

## 0.15 (June 25, 2015)

 * Moved cookie jar implementation from `FileGetContents` client to a browser
   listener
 * Added `DigestAuthListener`

## 0.14 (June 2, 2015)

 * Changed default HTTP protocol version to 1.1
 * Added verify host control to `AbstractClient`
