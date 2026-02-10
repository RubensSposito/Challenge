<?php

declare(strict_types=1);

namespace App\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function FastRoute\simpleDispatcher;

final class Router
{
    private Dispatcher $dispatcher;

    /**
     * @var \Closure(class-string): object
     */
    private readonly \Closure $resolver;

    /**
     * @param array<string, array{0: class-string, 1: string}> $routes
     * @param callable(class-string): object $resolver
     */
    public function __construct(array $routes, callable $resolver)
    {
        // Aqui eu converto callable em Closure para armazenar na property (PHP não permite property typed callable)
        $this->resolver = $resolver instanceof \Closure ? $resolver : \Closure::fromCallable($resolver);

        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) use ($routes): void {
            foreach ($routes as $key => $handler) {
                [$method, $path] = explode(' ', $key, 2);
                $r->addRoute($method, $path, $handler);
            }
        });
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        if ($routeInfo[0] === Dispatcher::NOT_FOUND) {
            return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'Rota não encontrada'
            ]));
        }

        if ($routeInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return new Response(405, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'Método não permitido'
            ]));
        }

        [, $handler, $vars] = $routeInfo;

        [$class, $method] = $handler;

        $controller = ($this->resolver)($class);

        return $controller->$method($request, $vars);
    }
}
