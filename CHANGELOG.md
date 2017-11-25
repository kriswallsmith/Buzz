# Change log

## 0.16.0

### Changed

* ClientInterface supports PSR7 requests and responses. 
* Removed type hint for second parameter for `AbstractCurl::prepare`.
* Removed type hint for second parameter for `RequestException::setRequest`.
* `RequestException` supports both PSR-7 requests and Buzz requests. 
* `Response::getProtocolVersion` will return a string and not a float. 

### Added 

* Added Request and Response converters
* Added `Curl::sendRequest()`, `MultiCurl::sendRequest()` and `FileGetContents::sendRequest()` that
  supports sending PSR-7 requests.   

### Deprecated

*`Curl::send()`, `MultiCurl::send()` and `FileGetContents::send()` in favor for `sendRequest()`. 

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
