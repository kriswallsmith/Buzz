# Buzz documentation

Buzz is a simple and lightweight HTTP client which is easy to use. This page is 
the index of the documentation. Please use the table of contents below to start
reading. 

* [Browser](#browser)
* [Submit forms](#submit-a-form) 
* [Client](/doc/client.md)
* [Middleware](/doc/middleware.md) 
* [Symfony Bundle](/doc/symfony.md) 


## Browser

The Browser is the high-level object to send HTTP requests. Main focus is on simplicity. 

When a `Browser` in constructed you have to select a [Client](/doc/client.md) to use. The 
`FileGetContents` client is used by default. See example of how
to use the Bowser: 

```php
use Buzz\Browser;
use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;

$client = new FileGetContents(new Psr17Factory());
$browser = new Browser($client, new Psr17Factory());

$response = $browser->get('https://example.com');
$response = $browser->get('https://example.com', ['User-Agent'=>'Buzz']);
$response = $browser->post('https://example.com', ['User-Agent'=>'Buzz'], 'http-post-body');

$response = $browser->head('https://example.com');
$response = $browser->patch('https://example.com');
$response = $browser->put('https://example.com');
$response = $browser->delete('https://example.com');


$response = $browser->request('GET', 'https://example.com');
```

You do also have a function to send PSR-7 requests. 

```php
use Nyholm\Psr7\Request;

$request = new Request('GET', 'https://google.com/foo');
$response = $browser->sendRequest($request)
```

## Submit a form

With Buzz you have built in support for posing forms. You could of course create your own PSR-7 request and posting it 
as you normally would. But it might be easier to use the `Browser::submit()` function or the `FormRequestBuilder`. 

Below is an example how to use `Browser::submit()` to upload a file. 

```php
$browser->submitForm('https://example.com/foo', [
    'user' => 'Kris Wallsmith',
    'image' => [
        'path'=>'/path/to/image.jpg'
      ],
]);
``` 

```php
$browser->submitForm('https://example.com/foo', [
    'user[name]' => 'Kris Wallsmith',
    'user[image]' => [
        'path'=>'/path/to/image.jpg',
        'filename' => 'my-image.jpg',
        'contentType' => 'image/jpg',
      ],
]);
``` 

### Using the FormRequestBuilder

If you have a large from or you want to build your request in a structured way you may use the `FormRequestBuilder`.

```php
use Buzz\Message\FormRequestBuilder;

$builder = new FormRequestBuilder();
$builder->addField('user[name]', 'Kris Wallsmith');
$builder->addFile('user[image]', '/path/to/image.jpg', 'image/jpg', 'my-image.jpg');
$builder->addFile('cover-image', '/path/to/cover.jpg');

$browser->submitForm('https://example.com/foo', $builder->build());
``` 

---

Continue reading about [Clients](/doc/client.md).
