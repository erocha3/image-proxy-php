<?php

namespace ImageProxyPHP;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Router
{
    private $dispatcher;
    private $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $this->dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/my-images/{imageName}', 'serve_image');
        });
    }

    public function route($httpMethod, $uri)
    {
        $uri = explode('?', $uri, 2)[0];
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found";
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                header("HTTP/1.0 405 Method Not Allowed");
                echo "405 Method Not Allowed";
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                if ($handler === 'serve_image') {
                    $imageProxy = $this->serviceManager->get('imageProxy');
                    $width = $_GET['width'] ?? null;
                    $height = $_GET['height'] ?? null;
                    $type = $_GET['type'] ?? null;
                    $imageProxy->serveImage($vars['imageName'], $width, $height, $type);
                }
                break;
        }
    }
}