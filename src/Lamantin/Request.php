<?php namespace Lamantin;

use GuzzleHttp\Client as Guzzle;
use Psr\Http\Message\ResponseInterface;

class Request
{
    /**
     * @var Guzzle
     */
    private $guzzle;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param Guzzle $guzzle
     */
    public function __construct(Guzzle $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @param string $url
     */
    public function request($url)
    {
        $this->response = $this->guzzle->request('GET', $url);

        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getContent()
    {
        return $this->getResponse()->getBody();
    }
}
