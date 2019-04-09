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

use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;

class EmailHandler implements RequestHandlerInterface
{

       /** @var string */
    private $containerName;

    /** @var Router\RouterInterface */
    private $router;

    /** @var null|TemplateRendererInterface */
    private $template;

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

                $email = trim( $body->email);
                $name = trim( $body->name );
                $subject = trim(  $body->subject );
                $message = trim(  $body->message );

                if (strlen($name) > 100 || strlen($name) === 0 || $name === "") {

                    return new JsonResponse(['nameError' => true]);

                }

                else if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {

                    return new JsonResponse(['emailError' => true]);

                }

                else if (strlen($subject) > 500 || strlen($subject) === 0 || $subject === "") {

                    return new JsonResponse(['subjectError' => true]);

                }

                else if (strlen($message) > 500 || strlen($message) === 0 || $message === "") {

                    return new JsonResponse(['messageError' => true]);

                }

                else {
                    
                    $html_msg = htmlentities($message);
                    $html_email = htmlentities($email);
                    $htm_name = htmlentities($name);
                    $html_subject = htmlentities($subject);

                    $safe_msg = filter_var($html_msg, FILTER_SANITIZE_STRING);
                    $safe_email = filter_var($html_email, FILTER_SANITIZE_STRING);
                    $safe_name = filter_var($htm_name, FILTER_SANITIZE_STRING);
                    $safe_subject = filter_var($html_subject, FILTER_SANITIZE_STRING);
         
                    $mail = new Mail\Message();
                    
                    $mail->getHeaders()->addHeaderLine('MIME-Version', '1.0');
                    $mail->getHeaders()->addHeaderLine('Content-Transfer-Encoding', '8bit');
                    $mail->getHeaders()->addHeaderLine('X-Mailer:', 'PHP/'.phpversion()
                    );
                    $mail->setBody($safe_msg);
                    $mail->setFrom($safe_email, $safe_name);
                    $mail->setReplyTo('mirza@kravmagatuzla.ba', 'Mirza Malkocevic');
                    $mail->addTo('mirza@kravmagatuzla.ba', 'Mirza Malkocevic');
                    $mail->setSubject($safe_subject);

                    $transport = new SmtpTransport();;
                    $transport->send($mail);

                    return new JsonResponse(['sent' => true]);

                }

            }

            if ( $method === "GET" ) {

                return new HtmlResponse($this->template->render('app::home-page.html.twig'));

            }

    }

}
