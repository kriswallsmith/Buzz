Buzz is a lightweight PHP 5.3 library for issuing HTTP requests.

    $browser = new Buzz\Browser();
    $response = $browser->get('http://www.google.com');

    echo $browser->getJournal()->getLastRequest()."\n";
    echo $response;

You can also use the low-level HTTP classes directly.

    use Buzz\Message;
    use Buzz\Client;

    $request = new Message\Request('HEAD', '/', 'http://google.com');
    $response = new Message\Response();

    $client = new Client\FileGetContents();
    $client->send($request, $response);

    echo $request;
    echo $response;

Before doing any of this you need to register the Buzz class loader.

    require_once 'Buzz/ClassLoader.php';
    Buzz\ClassLoader::register();

Buzz is tested using PHPUnit. The run the test suite, execute the following
command:

    $ phpunit test/
