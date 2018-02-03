# Submit a form

With Buzz you have built in support for posing forms. You could of course create your own PSR-7 request and post it 
as you normally would. But it might be easier to use the `Browser::submit()` function or the `FormRqquest`. 

Below is an example how to use `Browser::submit()` to upload a file. 

```php
$browser->submit('http://example.com/foo', [
    'user[name]' => 'Kris Wallsmith',
    'user[image]' => new FormUpload('/path/to/image.jpg',
]);
``` 

Here is an example doing exatly the same with the (deprecated) `FormRequest`. 

```php
$request = new FormRequest();
$request->setField('user[name]', 'Kris Wallsmith');
$request->setField('user[image]', new FormUpload('/path/to/image.jpg'));
$request->setResource('http://example.com/foo');

$browser->send($request);
``` 