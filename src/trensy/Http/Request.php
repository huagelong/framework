<?php
/**
 * http request 类库
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Http;

use Trensy\Http\HttpBase\ParameterBag;
use Trensy\Http\HttpBase\Request as BaseRequest;

class Request extends BaseRequest
{

    /**
     * 初始化
     * Request constructor.
     * @param array $swooleRequest
     */
    public function __construct(RequestAbstract $request)
    {
        $get = $request->get();
        $post = $request->post();
        $attributes = [];
        $cookie = $request->cookie();
        $files = $request->files();
        $requestServer = $request->server();
        $requestRawContent = $request->rawContent();
        $server = isset($requestServer) ? array_change_key_case($requestServer, CASE_UPPER) : [];
        $requestHeader = $request->header();
        if (isset($requestHeader)) {
            foreach ($requestHeader as $key => $value) {
                $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                $server[$newKey] = $value;
            }
        }

        parent::__construct($get, $post, $attributes, $cookie, $files, $server);

        // parse http body
        $contentType = $this->headers->get('CONTENT_TYPE');
        $requestMethod = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $this->content = $requestRawContent;
            $data = [];
            if (0 === strpos($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($this->content, $data);
            }
            if ($data) {
                $this->request = new ParameterBag($data);
            }
        }

    }
}