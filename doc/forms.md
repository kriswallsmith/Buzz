# Submit a form

With Buzz you have built in support for posing forms. You could of course create your own PSR-7 request and post it 
as you normally would. But it might be easier to use the `Browser::submit()` function or the `FormRqquest`. 

Below is an example how to use `Browser::submit()` to upload a file. 

```php
$browser->submitForm('http://example.com/foo', [
    'user[name]' => 'Kris Wallsmith',
    'user[image]' => [
        'path'=>'/path/to/image.jpg'
      ],
]);
``` 

```php
$browser->submitForm('http://example.com/foo', [
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
$builder = new FormRquestBuilder();
$builder->addField('user[name]', 'Kris Wallsmith');
$builder->addFile('user[image]', '/path/to/image.jpg', 'image/jpg', 'my-image.jpg');
$builder->addFile('cover-image', '/path/to/cover.jpg');

$browser->submitForm('http://example.com/foo', $builder->build());
``` 
