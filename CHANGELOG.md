# Change log

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
