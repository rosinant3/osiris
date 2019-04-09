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

use Doctrine\ORM\EntityManager;
use App\Entity\Blog2;
use App\Entity\Blog;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class EditGalleryHandler implements RequestHandlerInterface
{
    
    private $entityManager;

    public function __construct($entityManager) {
        
        $this->entityManager = $entityManager;

    }

    private function rm_r($folder,
    $keepRootFolder = false)
    {
        // Handle bad arguments.
        if (empty($folder) || !file_exists($folder)) {
            return true; // No such file/folder exists.
        } elseif (is_file($folder) || is_link($folder)) {
            return @unlink($folder); // Delete file/link.
        }

        // Delete all children.
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$action($fileinfo->getRealPath())) {
                return false; // Abort due to the failure.
            }
        }

        // Delete the root folder itself?
        return (!$keepRootFolder ? @rmdir($folder) : true);
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        $method = $request->getMethod();
        $body = $request->getParsedBody();

        if ( $method === "POST" ) {

            $type = $body["type"];

            if ($type === "editTitle") {

                $title = trim($body['value']);
                $gallery = $body['id'];

                if ($title === "" || strlen($title) > 100 ) {

                    return new JsonResponse([

                        'titleError' => true,
                 
                    ]); 

                }

                else {

                    try {

                        $qb = $this->entityManager->createQueryBuilder();
                        $qb->update('App\Entity\Blog2', 'b');
                        $qb->where('b.id = :id');
                        $qb->set('b.title', ':title');
                        $qb->setParameter('id', $gallery);
                        $qb->setParameter('title', $title);
                        $qb->getQuery()->execute();

                        return new JsonResponse([

                            'deleted' => true,
                 
                        ]);
                   
                    } catch (Exception $e) {

                        return new JsonResponse([

                        'error' => $e->getMessage(),
                 
                        ]); 

                    }

                }

            }

            if ($type === "editDescription") {

                $description = trim($body['value']);
                $gallery = $body['id'];

                if ($description === "" || strlen($description) > 500 ) {

                    return new JsonResponse([

                        'descriptionError' => true,
                 
                    ]); 

                }

                else {

                    try {

                        $qb = $this->entityManager->createQueryBuilder();
                        $qb->update('App\Entity\Blog2', 'b');
                        $qb->where('b.id = :id');
                        $qb->set('b.description', ':description');
                        $qb->setParameter('id', $gallery);
                        $qb->setParameter('description', $description);
                        $qb->getQuery()->execute();

                        return new JsonResponse([

                            'deleted' => true,
                 
                        ]);
                   
                    } catch (Exception $e) {

                        return new JsonResponse([

                        'error' => $e->getMessage(),
                 
                        ]); 

                    }

                }
                
            }

            if ($type === "removePicture") {

                $path = $_SERVER['DOCUMENT_ROOT'] . "/public" . $body["directory"];
                $fileName = $body["fileName"];
                $id = $body["id"];
                $file = $path . $fileName;
                $thumbnail =  $path . "/thumbnail/" . $body["fileName"];

                $unlinkFile = unlink($file);
                $unlinkThumbnail = unlink($thumbnail);

                if ($unlinkFile === true && $unlinkThumbnail === true) {

                    try {

                        $qb = $this->entityManager->createQueryBuilder();
                        $qb->delete('App\Entity\Blog', 'b');
                        $qb->where('b.id = :id');
                        $qb->setParameter('id', $id);
                        $qb->getQuery()->execute();

                        return new JsonResponse([

                            'deleted' => true,
                     
                        ]);
                       
                    } catch (Exception $e) {

                        return new JsonResponse([

                            'error' => $e->getMessage(),
                     
                        ]); 

                    }
                    

                }

                else {

                    return new JsonResponse([

                        'error' => true,
                     
                    ]);

                }

            }

            else if ($type === "removeGallery") {

                $path = $_SERVER['DOCUMENT_ROOT'] . "/public" . $body["directory"];
                $id = $body["id"];
                $thumbnailPath = $_SERVER['DOCUMENT_ROOT'] . "/public" . $body["directory"] . "/thumbnail";

                if (isset($body["fileName"])) {
 
                    $fileName = $body["fileName"];
                    $file = $path . $fileName;
                    $thumbnail = $path . "/thumbnail/" . $body["fileName"];

                    $unlinkFile = unlink($file);
                    $unlinkThumbnail = unlink($thumbnail);

                    if ($unlinkFile === true && $unlinkThumbnail === true ) {

                        $rmThumbnail = rmdir($thumbnailPath);
                        $rmPath = rmdir($path);
    
                        if ($rmThumbnail === true && $rmPath === true) {

                            try {

                                $qb2 = $this->entityManager->createQueryBuilder();
                                $qb2->delete('App\Entity\Blog', 'b');
                                $qb2->where('b.gallery = :id');
                                $qb2->setParameter('id', $id);
                                $qb2->getQuery()->execute();

                                $qb = $this->entityManager->createQueryBuilder();
                                $qb->delete('App\Entity\Blog2', 'b');
                                $qb->where('b.id = :id');
                                $qb->setParameter('id', $id);
                                $qb->getQuery()->execute();

        
                                return new JsonResponse([
        
                                    'deleted' => true,
                             
                                ]);
                               
                            } catch (Exception $e) {

                                return new JsonResponse([
    
                                    'error' => true,
                             
                                ]);

                            }
    
                        } else {
    
                                
                            return new JsonResponse([
    
                                'error' => true,
                         
                            ]);
    
                        }



                    }

            
                } else {

                    $path = $_SERVER['DOCUMENT_ROOT'] . "/public" . $body["directory"];
                    $thumbnailRemove = $this->rm_r($path . "thumbnail");
                    
                    if ($thumbnailRemove) {

                            $remove = $this->rm_r($path);

                            if ($remove) {

                                try {

                                    $gallery = $this->entityManager->find('App\Entity\Blog2', $id);
                                    $this->entityManager->remove($gallery);
                                    $this->entityManager->flush();


                                    return new JsonResponse([
    
                                        'deleted' =>   true,
                                    
                                    ]);
                                   
                                } catch (Exception $e) {
            
                                    return new JsonResponse([
            
                                        'error' => $e->getMessage(),
                                 
                                    ]); 
            
                                }

                            }

                            else {

                                return new JsonResponse([
    
                                    'error' =>   true,
                                    
                                ]);

                            }

                    }

                    else {

                        return new JsonResponse([
    
                            'errorThumbnail' => true,
                     
                        ]);

                    }


                }


            }


        }

    }
}
