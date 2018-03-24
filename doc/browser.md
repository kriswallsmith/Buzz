# Browser

The Browser is the high-level object to send HTTP requests. Main focus is on simplicity. 

When a `Browser` in constructed you have to select a [Client](/doc/client.md) to use. The 
`FileGetContents` client is used by default. See example of how
to use the Bowser: 

```php
$browser = new Buzz\Browser();
$response = $browser->get('https://example.com');
$response = $browser->get('https://example.com', ['User-Agent'=>'Buzz']);
$response = $browser->post('https://example.com', ['User-Agent'=>'Buzz'], 'http-post-body');
$response = $browser->head('https://example.com')
$response = $browser->patch('https://example.com')
$response = $browser->put('https://example.com')
$response = $browser->delete('https://example.com')
$response = $browser->delete('https://example.com')
```

You do also have a function to send PSR-7 requests. 

```php
$request = new PSR7Request('GET', 'https://google.com/foo');
$response = $browser->sendRequest($request)
```

If you want an easy way to submit forms. Check out [from documentation](/doc/forms.md).  