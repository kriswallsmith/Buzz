<?php

declare(strict_types=1);

namespace Buzz\Test\Integration\Httplug;

use Buzz\Client\MultiCurl;
use Nyholm\Psr7\Factory\Psr17Factory;

class MultiCurlIntegrationTest extends BaseIntegrationTest
{
    protected function createHttpAdapter()
    {
        return new MultiCurl(new Psr17Factory(), []);
    }

    /**
     * @dataProvider requestProvider
     * @group        integration
     */
    public function testSendRequest($method, $uri, array $headers, $body)
    {
        if (\defined('HHVM_VERSION')) {
            static::markTestSkipped('This test can not run under HHVM');
        }
        if (null !== $body && \in_array($method, ['GET', 'HEAD', 'TRACE'], true)) {
            static::markTestSkipped('cURL can not send body using '.$method);
        }
        parent::testSendRequest($method, $uri, $headers, $body);
    }

    /**
     * @dataProvider requestWithOutcomeProvider
     * @group        integration
     */
    public function testSendRequestWithOutcome($uriAndOutcome, $protocolVersion, array $headers, $body)
    {
        if (null !== $body && '1.0' !== $protocolVersion) {
            $this->markTestSkipped('cURL can not send body using GET');
        }
        parent::testSendRequestWithOutcome($uriAndOutcome, $protocolVersion, $headers, $body);
    }
}
