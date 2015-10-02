<?php namespace Lamantin;

use Psr\Http\Message\ResponseInterface;

class Request
{
    /**
     * @var string
     */
    private $response;

    /**
     * @param string $url
     */
    public function request($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $this->response = curl_exec($ch);
        curl_close($ch);

        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return (string) $this->getResponse();
    }
}
