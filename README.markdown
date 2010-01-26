Buzz is a lightweight PHP 5.3 library for issuing HTTP requests.

    require_once 'Buzz/ClassLoader.php';
    Buzz\ClassLoader::getInstance()->register();

    $browser = new Buzz\Browser();
    $response = $browser->get('http://www.google.com');

    echo $response;

You can also use the low-level HTTP classes directly.

    require_once 'Buzz/ClassLoader.php';
    Buzz\ClassLoader::getInstance()->register();

    $request = new Buzz\Request('HEAD', '/', 'http://google.com');
    $response = new Buzz\Response();

    $client = new Buzz\FileGetContentsClient();
    $client->send($request, $response);

    echo $request;
    echo $response;
