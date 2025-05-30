<?php

namespace modAI\API;

use Cassandra\Exception\UnauthorizedException;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\modAI;
use modAI\Services\Response\AIResponse;
use modAI\Settings;
use modAI\Utils;
use MODX\Revolution\modX;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class API
{
    protected modX $modx;

    /** @var modAI|null  */
    protected $modAI = null;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;

        if ($this->modx->services->has('modai')) {
            $this->modAI = $this->modx->services->get('modai');
        }

        if ($this->modAI) {
            if (!$this->modx->hasPermission('modai_client')) {
                throw APIException::unauthorized();
            }

            foreach ($this->modAI->getUILexiconTopics() as $topic) {
                $this->modx->lexicon->load($topic);
            }
        }
    }

    public function handleRequest(ServerRequestInterface $request): void
    {
        try {
            if ($this->modAI === null) {
                throw APIException::unauthorized();
            }

            $request = $this->parseJsonBody($request);

            switch ($request->getMethod()) {
                case 'GET':
                    $this->get($request);
                    break;
                case 'POST':
                    $this->post($request);
                    break;
                case 'DELETE':
                    $this->delete($request);
                    break;
                default:
                    throw APIException::methodNotAllowed();
            }
        } catch (LexiconException $e) {
            self::returnError($this->modx->lexicon($e->getLexicon(), $e->getLexiconParams()), 'Request Failed');
        } catch (APIException $e) {
            self::returnErrorFromAPIException($e);
        } catch (\Exception $e) {
            self::returnError($e->getMessage(), 'Request Failed');
        }
    }

    /**
     * @throws Throwable
     */
    protected function post(ServerRequestInterface $request): void
    {
        throw APIException::methodNotAllowed();
    }

    /**
     * @throws Throwable
     */
    protected function get(ServerRequestInterface $request): void
    {
        throw APIException::methodNotAllowed();
    }

    /**
     * @throws Throwable
     */
    protected function delete(ServerRequestInterface $request): void
    {
        throw APIException::methodNotAllowed();
    }

    public static function returnError($detail, $title = '', $statusCode = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        echo json_encode([
            'statusCode' => $statusCode,
            'title' => $title,
            'detail' => $detail,
        ]);
    }

    public static function returnErrorFromAPIException(APIException $e)
    {
        http_response_code($e->getStatusCode());
        header('Content-Type: application/json');

        echo json_encode([
            'statusCode' => $e->getStatusCode(),
            'title' => $e->getTitle(),
            'detail' => $e->getDetail(),
        ]);
    }

    private function parseJsonBody(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $request;
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (stripos($contentType, 'application/json') !== 0) {
            throw new \Exception('Content-Type must be application/json');
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON');
        }

        return $request->withParsedBody($data);
    }

    protected function success($data): void
    {
        http_response_code(200);
        header('Content-Type: application/json');

        echo json_encode($data);
    }

    protected function proxyAIResponse(AIResponse $aiResponse)
    {
        $headerStream = (int)$aiResponse->isStream();
        header("x-modai-service: {$aiResponse->getService()}");
        header("x-modai-model: {$aiResponse->getModel()}");
        header("x-modai-parser: {$aiResponse->getParser()}");
        header("x-modai-stream: $headerStream");

        $onServer = intval(Settings::getApiSetting($this->modx, $aiResponse->getService(), 'execute_on_server')) === 1;
        if (!$onServer) {
            header("x-modai-proxy: 0");
            $this->success([
                'forExecutor' => [
                    'service' => $aiResponse->getService(),
                    'model' => $aiResponse->getModel(),
                    'stream' => $aiResponse->isStream(),
                    'parser' => $aiResponse->getParser(),
                    'url' => $aiResponse->getUrl(),
                    'contentType' => $aiResponse->getContentType(),
                    'headers' => $aiResponse->getHeaders(),
                    'body' => $aiResponse->getBody(),
                    'binary' => $aiResponse->getBinary(),
                ]
            ]);
            return;
        }

        header("x-modai-proxy: 1");

        $headers = [];
        $boundary = '--------------------------' . microtime(true);
        $isFormData = false;
        $contentType = $aiResponse->getContentType();

        if (strtolower($contentType) === 'multipart/form-data') {
            $isFormData = true;
            $headers[] = "Content-Type:$contentType; boundary=$boundary";
        } else {
            $headers[] = "Content-Type:$contentType";
        }

        foreach ($aiResponse->getHeaders() as $key => $value) {
            $headers[] = "$key:$value";
        }

        $body = '';
        if (!$isFormData) {
            $body = json_encode($aiResponse->getBody());
        } else {
            $binary = $aiResponse->getBinary();
            foreach ($binary as $name => $files) {
                foreach ($files as $i => $file) {
                    $body .= '--' . $boundary . "\r\n";
                    $body .= 'Content-Disposition: form-data; name="'.$name.'[]"; filename="source_image_' . $i . '.png"' . "\r\n";
                    $body .= 'Content-Type: ' . $file['mimeType'] . "\r\n";
                    $body .= "\r\n";
                    $body .= base64_decode($file['base64']) . "\r\n";
                }
            }

            $input = $aiResponse->getBody();
            foreach ($input as $name => $value) {
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n";
                $body .= "\r\n";
                $body .= $value . "\r\n";
            }

            $body .= '--' . $boundary . '--' . "\r\n";
        }

        $ch = curl_init($aiResponse->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, !$aiResponse->isStream());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $statusCode = 200;
        $headersSent = false;
        $bodyBuffer = '';


        if ($aiResponse->isStream()) {
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header_line) use (&$statusCode) {
                if (strpos($header_line, 'HTTP/') === 0) {
                    preg_match('#HTTP/\S+ (\d+)#', $header_line, $matches);
                    if (isset($matches[1])) {
                        $statusCode = (int)$matches[1];
                    }
                }
                return strlen($header_line);
            });

            header('Content-Type: text/event-stream');
            header('Connection: keep-alive');
            header('Cache-Control: no-cache');
            flush();
            ob_flush();

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) use (&$statusCode, &$headersSent, &$bodyBuffer) {
                if ($statusCode >= 400) {
                    $bodyBuffer .= $chunk;
                    return strlen($chunk);
                } else {
                    if (!$headersSent) {
                        header('Content-Type: text/event-stream');
                        header('Connection: keep-alive');
                        header('Cache-Control: no-cache');
                        flush();
                        ob_flush();
                        $headersSent = true;
                    }

                    echo $chunk;
                    flush();
                    ob_flush();
                    return strlen($chunk);
                }
            });
        }

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error_msg);
        }

        if (!$aiResponse->isStream()) {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $bodyBuffer = $response;
        }

        http_response_code($statusCode);

        if ($statusCode >= 400) {
            header('Content-Type: application/json');
            echo $bodyBuffer;
            return;
        }

        if (!$aiResponse->isStream()) {
            header("Content-Type: application/json");
            echo $response;
            return;
        }


        curl_close($ch);
    }
}
