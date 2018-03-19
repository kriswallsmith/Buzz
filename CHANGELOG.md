# Change log

The change log shows what have been Added, Changed, Deprecated and Removed between versions. 

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
