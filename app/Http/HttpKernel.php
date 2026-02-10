<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\JsonBodyMiddleware;
use App\Http\Middleware\ProblemDetailsMiddleware;
use App\Infrastructure\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HttpKernel implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private array $middleware;

    private function __construct(
        private readonly Router $router,
        private readonly ServerRequestCreatorInterface $requestCreator,
        array $middleware
    ) {
        $this->middleware = $middleware;
    }

    public static function boot(): self
    {
        $container = Container::build();

        $psr17 = new Psr17Factory();
        $creator = new ServerRequestCreator($psr17, $psr17, $psr17, $psr17);

        $router = $container->get(Router::class);

        $middleware = [
            $container->get(CorrelationIdMiddleware::class),
            $container->get(JsonBodyMiddleware::class),
            $container->get(ProblemDetailsMiddleware::class),
        ];

        return new self($router, $creator, $middleware);
    }

    /**
     * Aqui eu crio a request a partir dos globals e inicio o pipeline.
     * Esse método é o entrypoint para o public/index.php.
     */
    public function handleRequest(): ResponseInterface
    {
        $request = $this->requestCreator->fromGlobals();
        return $this->handle($request);
    }

    /**
     * Aqui eu implemento o contrato do PSR-15 corretamente.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = array_reduce(
            array_reverse($this->middleware),
            fn (RequestHandlerInterface $next, MiddlewareInterface $mw) => new class($mw, $next) implements RequestHandlerInterface {
                public function __construct(private MiddlewareInterface $mw, private RequestHandlerInterface $next) {}

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->mw->process($request, $this->next);
                }
            },
            new class($this->router) implements RequestHandlerInterface {
                public function __construct(private Router $router) {}

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->router->dispatch($request);
                }
            }
        );

        return $handler->handle($request);
    }
}
