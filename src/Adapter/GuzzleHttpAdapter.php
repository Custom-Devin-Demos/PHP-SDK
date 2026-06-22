<?php
namespace FedExCrossBorder\Adapter;

use FedExCrossBorder\Exception\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class GuzzleHttpAdapter implements AdapterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function get($url)
    {
        try {
            $this->response = $this->client
                ->get(
                    $url,
                    [
                        'headers' => $this->headers
                    ]
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }
        return $this->response->getBody()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($url)
    {
        try {
            $this->response = $this->client
                ->delete(
                    $url,
                    [
                        'headers' => $this->headers
                    ]
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }
        return $this->response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function put($url, $content = '')
    {
        try {
            $this->response = $this->client
                ->put(
                    $url,
                    [
                        'headers' => $this->headers,
                        'body' => $content
                    ]
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }
        return $this->response->getBody()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function post($url, $body = '')
    {
        try {
            $this->response = $this->client
                ->post(
                    $url,
                    [
                        'headers' => $this->headers,
                        'body' => $body
                    ]
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }
        return $this->response->getBody()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function patch($url, $body = '')
    {
        try {
            $this->response = $this->client
                ->patch(
                    $url,
                    [
                        'headers' => $this->headers,
                        'body' => $body
                    ]
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }

        return $this->response->getBody()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function postBodyParams($url, $params = [], $headers = null)
    {
        try {
            if($headers == null) {
                $headers = $this->headers;
            }

            $options = [
                'headers' => $headers,
                'form_params' => $params,
            ];

            $this->response = $this->client
                ->post(
                    $url,
                    $options
                )
            ;
        } catch (RequestException $e) {
            $this->response = $e->getResponse();
            $this->handleError($e);
        }
        return $this->response->getBody()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestResponseHeaders()
    {
        if (null === $this->response) {
            return;
        }
        return [
            'reset' => (int) $this->response->getHeaderLine('RateLimit-Reset'),
            'remaining' => (int) $this->response->getHeaderLine('RateLimit-Remaining'),
            'limit' => (int) $this->response->getHeaderLine('RateLimit-Limit'),
        ];
    }

    /**
     * @param RequestException $e
     * @throws HttpException
     */
    protected function handleError(RequestException $e)
    {
        $body = (string) $this->response->getBody();
        $code = (int) $this->response->getStatusCode();
        $content = json_decode($body);
        $message = isset($content->detail) ? $content->detail : $e->getMessage();
        throw new HttpException(!empty($message) ? $message: 'Request not processed.', $code, $content);
    }

    /**
     * @inheritdoc
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @inheritdoc
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
}
