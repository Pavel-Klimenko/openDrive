<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/22
 * Time: 23:35
 */

namespace App\Controller;


use App\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller;

use Symfony\Component\Finder\Finder;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;



class MainPageController extends AbstractController
{

    public $helper;
    public $fileSystem;


    public function __construct(Services\HelperService $helper, Services\FileSystemService $fileSystem) {
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
    }


    public CONST FILE_EXTENSIONS = [
        'IMAGES' => ['jpg', 'png', 'jpeg', 'webp'],
        'AUDIO' => ['mp3', 'aac', 'wav', 'flac'],
        'VIDEO' => ['mp4', 'avi', 'mov', 'mpeg'],
    ];


    /**
     * @Route("/")
     */
    public function renderMainPage()
    {

        return $this->render('main-page.html.twig', [
            'number' => 1,
        ]);

    }


    /**
     * @Route("/show-folder")
     */
    public function showFolder()
    {

        $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/';

        $finder = new Finder();

        $finder->in($storagePath);


        $arrFiles = [];
        $arrDirectories = [];

        if ($finder->hasResults()) {
            foreach ($finder as $entity) {
                $entityName = $entity->getRelativePathname();

                if (str_contains($entityName, '.')) {
                    $arrFiles[] = $entityName;
                } else {
                    $arrDirectories[] = $entityName;
                }

            }
        }

        var_dump($arrFiles);
        var_dump($arrDirectories);

        //TODO красиво вывести в шаблон
        //TODO в таблицу вместо иконок классы

        //getting files
        /*        $arrFiles = [];
                $finder->files()->in($storagePath);

                if ($finder->hasResults()) {
                    foreach ($finder as $file) {
                        $arrFiles[] = $file->getRelativePathname();
                    }
                }


                var_dump($arrDirectories);
                var_dump($arrFiles);*/


        return new Response(
            'showFolder',
            Response::HTTP_OK
        );


    }


    /**
     * @Route("/get-data/{directory}/{type}")
     */
    public function getFiles($directory, $type = false)
    {

        //TODO доработать получение конкретной папки

        switch ($type) {
            case "img":
                $arrFileExtensions = self::FILE_EXTENSIONS['IMAGES'];
                break;
            case "audio":
                $arrFileExtensions = self::FILE_EXTENSIONS['AUDIO'];
                break;
            case "video":
                $arrFileExtensions = self::FILE_EXTENSIONS['VIDEO'];
                break;
            default:
                $arrFileExtensions = false;
        }


        $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . $directory . '/';
        $finder = new Finder();
        $finder->files()->in($storagePath);
        $arrResult = [];


        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $fileName = $file->getRelativePathname();
                list($name, $extension) = explode('.', $fileName);


                if (is_array($arrFileExtensions)) {
                    //selected type of files
                    if (in_array($extension, $arrFileExtensions)) {
                        $arrResult[] = $fileName;
                    }
                } else {
                    //all files
                    $arrResult[] = $fileName;
                }


            }
        }

        $this->helper->prent($arrResult);
        //var_dump($arrResult);


        return new Response(
            'getFiles',
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/basket/")
     */
    public function getBasket()
    {
        $basketPath = $_SERVER['DOCUMENT_ROOT'] . '/storage/basket/';
        $finder = new Finder();
        $finder->in($basketPath);
        $arrResult = [];


        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $fileName = $file->getRelativePathname();
                $arrResult[] = $fileName;
            }
        }


        var_dump($arrResult);


        return new Response(
            'getBasket',
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/clean-basket/")
     */
    public function cleanBasket()
    {

        $basketPath = $_SERVER['DOCUMENT_ROOT'] . '/storage/basket/';
        $basket = scandir($basketPath);
        unset($basket[0], $basket[1]);


        if (count($basket) > 0) {
            //var_dump('НЕ ПУСТАЯ');
            $directoryIterator = new RecursiveDirectoryIterator($basketPath, FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($recursiveIterator as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        } else {
            //var_dump('ПУСТАЯ');
            return new Response(
                'Basket is empty',
                Response::HTTP_OK
            );
        }


        return new Response(
            'cleanBasket',
            Response::HTTP_OK
        );
    }



    /**
     * @Route("/create-folder")
     */
    public function createFolder()
    {

        $path = '/storage/user_files/newFolder21212';
        $this->fileSystem->createFolder($path);


        return new Response(
            'createFolder',
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/add-to-basket")
     */
    public function addToBasket()
    {

        $origin = '/storage/user_files/subDirectory1';


        $this->fileSystem->move($origin);

        return new Response(
            'addToBasket',
            Response::HTTP_OK
        );
    }






}


