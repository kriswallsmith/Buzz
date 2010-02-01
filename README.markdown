Buzz is a lightweight PHP 5.3 library for issuing HTTP requests.

    $browser = new Buzz\Browser();
    $response = $browser->get('http://www.google.com');

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

Buzz provides packages for connecting to third party APIs easily.

    use Buzz\Service\RightScale;

    $rightscale = new RightScale\API();
    $rightscale->setAccountId(123456);
    $rightscale->setUsername('me@example.com');
    $rightscale->setPassword('s3cr3t');

    $deployment = $rightscale->findDeploymentByNickname('production');
    $rightScript = $rightScript->findRightScriptByName('deploy');
    $deployment->findServersByNickname('/^application-/')->runScript($rightScript);
