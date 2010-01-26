Buzz is a PHP 5.3 library for issuing HTTP requests.

    <?php

    require_once 'Buzz/ClassLoader.php';
    Buzz\ClassLoader::getInstance()->register();

    $browser = new Buzz\Browser();
    $browser->get('http://www.google.com');

You can also use the low-level classes directly.

    <?php

    require_once 'Buzz/ClassLoader.php';
    Buzz\ClassLoader::getInstance()->register();

    $request = new Buzz\Request('HEAD', '/', 'http://google.com');
    $response = new Buzz\Response();

    $client = new Buzz\FileGetContentsClient();
    $client->send($request, $response);

    echo $request;
    echo $response;
