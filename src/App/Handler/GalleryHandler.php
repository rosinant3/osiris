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
use App\Entity\Blog;
use App\Entity\Blog2;

class GalleryHandler implements RequestHandlerInterface
{
    
    private $em;

    public function __construct($em) {
        
        $this->em = $em;

    }


    private function imageUploadRaw($image) {

        $currentDir = getcwd();
        $total;
        $uploadDirectory;

        $fileName = array();
        $uploadDir;
        $realImg;

        $uploadDir = $image['directory'];
        $gallery = $image['gallery'];
        $type = $image['type'];

        $total = count($image['images']['name']);
        $realImg = $image['images'];

        for ($i = 0; $i < $total; $i++) {

            $direc = $_SERVER['DOCUMENT_ROOT'] . "/public" .$uploadDir;

            $uploadPath = $direc . $realImg['name'][$i]; 
            $file_tmp = $realImg['tmp_name'][$i]; 

            if (file_exists($uploadPath) === false) {
            
                $didUpload = move_uploaded_file($file_tmp , $uploadPath);
                array_push($fileName, $realImg['name'][$i]);  

                if ($type === "images") {
            
                    $img = imagecreatefromstring(file_get_contents($uploadPath));
                    $scaled_img = imagescale($img, 640);
                    imagejpeg($scaled_img, $direc . "thumbnail/" . $realImg['name'][$i]);

                }
            }
            
        }

        $realInfo = array(  'files' => $fileName,
                            'directory' => $uploadDir,
                            'gallery' => $gallery
                        );

        return $realInfo;

    }


    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        $method = $request->getMethod();
        $body = $request->getParsedBody();

        if ( $method === "POST" ) {

            $images = $_FILES["files"];

            if (!isset($body["title"]) && !isset($body["description"])) {

                $gallery_id = $body["gallery"];
                $directory = $body["directory"];

                if ($this->fileHandler($images, $type = "images") || count($images["name"]) > 1) {

                    return new JsonResponse([

                        'imageError' => true
        
                    ]);

                } else {

                    $details = array(   

                        'directory' => $directory,
                        'images' => $images,
                        'type' => 'images',
                        'gallery' => $gallery_id

                    );
    
                    $realDetails = $this->imageUploadRaw($details);
                    $items = new Blog();
                   
                        foreach ($realDetails['files'] as $file) {

                            try {

                                $galleryReference = $this->em->getReference('App\Entity\Blog2', $realDetails['gallery']);
                                $emItems = $this->em;
                                $items->setFileName($file);
                                $items->setDirectory($realDetails['directory']);
                                $items->setGallery($galleryReference);
                                $emItems->persist($items);
                                $emItems->flush();
                                $emItems->clear();

                            } catch (Exception $e) {

                                return new JsonResponse([

                                    'error' => $e->getMessage(),
                             
                                ]); 

                            }

                        }

                        return new JsonResponse([

                            'uploaded' => $items->getId()
    
                        ]);

                    }



            } else {

            $title = trim($body["title"]);
            $description = trim($body["description"]);
            $date = trim($body["date"]);
            $type = trim($body["type"]);
            

            if ($title === "" || strlen($title) > 100) {

                return new JsonResponse([

                    'titleError' => true
    
                ]);

            }

            else if ($description === "" || strlen($description) > 500) {

                return new JsonResponse([

                    'descriptionError' => true
    
                ]);


            }

            else if ($this->fileHandler($images, $type) || count($images["name"]) > 30) {

                return new JsonResponse([

                    'imageError' => true
    
                ]);


            }

            else {

                $details = array(   
                    'title' => $title,
                    'description' => $description,
                    'date' => $date,
                    'images' => $images,
                    'type' => $type
                );

                $realDetails = $this->imageUpload($details);

                $gallery = new Blog2();
                $gallery->setDate($realDetails['date']);
                $gallery->setTitle($realDetails['title']);
                $gallery->setType($realDetails['type']);
                $gallery->setDescription($realDetails['description']);

                try {

                    $emGallery = $this->em;                 
                    $emGallery->persist($gallery);
                    $emGallery->flush();

                   
                } catch (Exception $e) {

                    return new JsonResponse([

                        'error' => $e->getMessage(),
                 
                    ]); 

                }


                foreach ($realDetails['files'] as $file) {

                    try {

                        $emItems = $this->em;
                        $items = new Blog();
                        $items->setFileName($file);
                        $items->setDirectory($realDetails['directory']);
                        $items->setGallery($gallery);
                        $emItems->persist($items);
                        $emItems->flush();
                        $emItems->clear("App\Entity\Blog");
    
                       
                    } catch (Exception $e) {

                        return new JsonResponse([

                            'error' => $e->getMessage(),
                     
                        ]); 
                        break;
    

                    }
                        
                    };

                      

                return new JsonResponse([

                    'uploaded' => true
    
                ]);

            }

        }

        }


        
    }

    private function fileHandler(array $file, string $type) {

        $error = false;
        $total = count($file['name']);
        $extensions;

        if ($type === "images") {

            $extensions = array("jpeg","jpg","png");

        }

        if ($type === "video") {

            $extensions = array("mp4","avi","webm");

        }

        for ($i = 0; $i < $total; $i++) {
        
            $file_name = $file['name'][$i];
            $file_size = $file['size'][$i];
            $file_tmp = $file['tmp_name'][$i];
            $file_type = $file['type'][$i];
            $file_explode = explode('.', $file_name);
            $file_ext= strtolower(end($file_explode));

            if (in_array($file_ext, $extensions) === false){

                $error = true;
                return $error;
                break;
                
             }
   
        }

        return $error;

    }

    private function imageUpload($image) {

        $currentDir = getcwd();
        $total;
        $uploadDirectory;

        $fileName = array();
        $uploadDir;
        $title = $image['title'];
        $date = $image['date'];
        $type = $image['type'];
        $description = $image['description'];
        $realImg;

        $uploadDir = '/uploads/' . $image['title'] . str_replace(' ', '', $date) . '/';

        $createDirectory = $_SERVER['DOCUMENT_ROOT'] . "/public" . $uploadDir;

        if (!file_exists($createDirectory)) {

            mkdir($createDirectory, 0777, true);
            mkdir($createDirectory . "/thumbnail", 0777, true);
                  
        }

        $uploadDirectory = $createDirectory;
        $total = count($image['images']['name']);
        $realImg = $image['images'];

        for ($i = 0; $i < $total; $i++) {

            $direc = $_SERVER['DOCUMENT_ROOT'] . "/public" . $uploadDir;

            $uploadPath = $uploadDirectory . $realImg['name'][$i]; 
            $file_tmp = $realImg['tmp_name'][$i];  
            $didUpload = move_uploaded_file($file_tmp , $uploadPath);
            array_push($fileName, $realImg['name'][$i]);  

            if ($type === "images") {
            
                $img = imagecreatefromstring(file_get_contents($uploadPath));
                $scaled_img = imagescale($img, 400);
                imagejpeg($scaled_img, $direc . "thumbnail/" . $realImg['name'][$i]);

            }
            
        }

        $realInfo = array(  'files' => $fileName,
                            'directory' => $uploadDir,
                            'title' => $title,
                            'description' => $description,
                            'date' => $date,
                            'type' => $type );

        return $realInfo;

    }
    
}

