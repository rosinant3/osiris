<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Plates\PlatesRenderer;
use Zend\Expressive\Router;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\ZendView\ZendViewRenderer;

class LoginHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

    /** @var string */
    private $username = "????";

    /** @var string */
    private $password = '????';

    public function __construct(
        string $containerName,
        Router\RouterInterface $router,
        ?TemplateRendererInterface $template = null
    ) {
        $this->containerName = $containerName;
        $this->router        = $router;
        $this->template      = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

            $method = $request->getMethod();

            if ( $method === "POST" ) {

                $body = json_decode($request->getBody()->getContents());

                $username = trim( $body->username);
                $password = trim( $body->password );

                if ($username === "" || strlen($username) > 20 || $username !== $this->username ) {

                    return new JsonResponse([
                        'usernameError' => $username
                    ]);


                }

                else if ($password === "" || strlen($password) > 20 || !password_verify($password, $this->password)) {

                    return new JsonResponse([
                        'passwordError' => true
                    ]);

                }

                else {

                    return new JsonResponse([
                        'passed' => true
                    ]);

                }
         
            }

        
    }
}
