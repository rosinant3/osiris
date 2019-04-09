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

class GetGalleryHandler implements RequestHandlerInterface
{
    
    private $entityManager;

    private $blog = Array();

    private $title; 
    private $description;
    private $date;
    private $type;
    private $items;
    private $id;

    private $fileName;
    private $directory;
    private $item_id;

    public function __construct($entityManager) {
        
        $this->entityManager = $entityManager;

    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        $method = $request->getMethod();
        $body = $request->getParsedBody();
        $pagination = Array();

        if ( $method === "POST" ) {

    		$per_page = $body["per_page"];
            $page = $body["current_page"];
            $offset = ($page - 1) * $per_page;

            $qbCount = $this->entityManager->createQueryBuilder();
            $query = $this->entityManager->createQueryBuilder();

            if (isset($body["title"])) {

                $title = trim($body["title"]);

                if (strlen($title) > 100 ) {

                    return new JsonResponse([

                        'titleError' => true,
                 
                    ]); 

                }

                else {

                    $qbCount->select('count(account.id)')
                    ->from('App\Entity\Blog2', 'account')
                    ->where('account.title LIKE :title')
                    ->setParameter('title', '%'.$title.'%');

                    $query->select(Array('g', 'i'))
                    ->from('App\Entity\Blog2', 'g')
                    ->innerJoin('g.items', 'i')
                    ->where('g.title LIKE :title')
                    ->addSelect('i')
                    ->setParameter('title', '%'.$body["title"].'%')
                    ->orderBy('g.date', 'DESC');

                }

            }

            if (!isset($body["title"])) {

                $qbCount->select('count(account.id)');
                $qbCount->from('App\Entity\Blog2', 'account');

                $query->select(Array('g', 'i'))
                ->from('App\Entity\Blog2', 'g')
                ->innerJoin('g.items', 'i')
                ->addSelect('i')
                ->orderBy('g.date', 'DESC');

            }

            $count = $qbCount->getQuery()->getSingleScalarResult();

            $pagination["total"] = intval($count);
			$pagination["per_page"] = $per_page;
			$pagination["offset"] = $offset;
			$pagination["current_page"] = $page;
            $pagination["from"] = $offset;
            
            $blog = new Paginator($query, $fetchJoinCollection = true);

            if ($pagination["total"] < $per_page) {

                $per_page = 30;

            }
            
            try {

                $blog->getQuery()
                ->setMaxResults( $per_page )
                ->setFirstResult( $offset )
                ->getResult();

                $data = Array();

                foreach ($blog as $value) {

                    $this->title = $value->getTitle();
                    $this->description = $value->getDescription();
                    $this->date = $value->getDate();
                    $this->type = $value->getType();
                    $this->items = $value->getItems();
                    $this->id = $value->getId();

                    $itemsArray = Array();

                    foreach ($this->items as $item) {

                        $itemObj = Array(

                            "id" => $item->getId(),
                            "directory" => $item->getDirectory(),
                            "fileName" => $item->getFilename()

                        );

                        array_push($itemsArray , $itemObj);

                    }

                    $post = Array(

                        "title" => $this->title,
                        "description" => $this->description,
                        "date" => $this->date,
                        "type" => $this->type,
                        "items" => $itemsArray,
                        "id" => $this->id

                    );

                    array_push($this->blog, $post);

                }

            $pagination["to"] = $offset +  sizeof($blog);
            $pagination["last_page"] = ceil($count / $per_page);

            return new JsonResponse([

                'pagination' => $pagination,
                'data' => $this->blog,
                'body' => $body

            ]);
    
               
            } catch (Exception $e)  { 

                return new JsonResponse([

                    'error' => $e->getMessage(),
             
                ]); 

            }

        }

    }
}
