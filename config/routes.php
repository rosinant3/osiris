<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Session\SessionPersistenceInterface;

/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    
    $app->get('/', App\Handler\HomePageHandler::class, 'home');

    $app->post('/login', 
    
    [App\Handler\LoginHandler::class], 
    'login');

    $app->post('/gallery/post',
    
        [Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Handler\GalleryHandler::class],
        "galleryPost"
    
    );

    $app->post('/gallery/edit',
    
    [Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
    App\Handler\EditGalleryHandler::class],
    "galleryEdit"

    );

    $app->post('/gallery/get',
    
        [Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Handler\GetGalleryHandler::class],
        "galleryGet"
    
    );

    $app->route('/mail', 
    
    [Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class, 
    App\Handler\EmailHandler::class], 

    ['GET', 'POST'],
    
    'EmailHandler');

    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');

};
