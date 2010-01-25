<?php

namespace Buzz;

class MockClient implements ClientInterface
{
  /**
   * @see ClientInterface
   */
  public function send(Request $request, Response $response)
  {
  }
}
