<?php

namespace Buzz;

interface ClientInterface
{
  /**
   * Populates the supplied response with the response for the supplied request.
   * 
   * @param Request  $request  A request object
   * @param Response $response A response object
   */
  public function send(Request $request, Response $response);
}
