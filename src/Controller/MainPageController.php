<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/22
 * Time: 23:35
 */

namespace App\Controller;


use App\Entity\Basket;
use App\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller;

use App\GlobalFunctions\Helper;

use Symfony\Component\Finder\Finder;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;



class MainPageController extends AbstractController
{


    public $fileSystem;


    /** @var EntityManagerInterface */
    private $entityManager;



    public function __construct(
        Services\FileSystemService $fileSystem,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
    }



    /**
     * @Route("/")
     */
    public function renderMainPage()
    {
        return $this->getFiles('/user_files');
    }


    /**
     * @Route("/show-folder/")
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
     * @Route("/{path}/{fileType}")
     */
    public function getFiles($path, $fileType = false)
    {

        //var_dump($path);
        //var_dump($fileType);


        if (str_contains($path, '-')) {
            $arLink = explode('-', $path);
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . implode('/', $arLink);
        } else {
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH .$path;
        }


        //var_dump($storagePath);


        $arrDirectories = $this->getStorageDirectories($storagePath);
        $arrFiles = $this->getStorageFiles($storagePath, $fileType);

         $response = [
             'folders' => $arrDirectories,
             'files' => $arrFiles,
             'current_path' => $path
         ];


        //var_dump($response);


/*        $ssss = $this->getUserDiskInfo();
        var_dump($ssss);*/



        return $this->render('user-data.html.twig', [
            'response' => $response,
        ]);

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
        $arrOrigin = explode('/', $origin);
        $itemName = end($arrOrigin);
        $target = '/storage/basket/' . $itemName;
        $type = (is_dir($_SERVER['DOCUMENT_ROOT'] . $origin)) ? 'folder' : 'file';


        $this->fileSystem->move($origin, $target);


        $basket = new Basket();
        $basket->setType($type);
        $basket->setPath($origin);
        $basket->setItem($itemName);

        // сообщите Doctrine, что вы хотите (в итоге) сохранить Продукт (пока без запросов)
        $this->entityManager->persist($basket);

        // действительно выполните запросы (например, запрос INSERT)
        $this->entityManager->flush();


        return new Response(
            'addToBasket',
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/restore-from-basket")
     */
    public function restoreFromBasket()
    {

        $itemName = 'subDirectory1';
        $basketRepository = $this->entityManager->getRepository(Basket::class);

        $basketItem = $basketRepository->findOneBy(['item' => $itemName]);


        $origin = '/storage/basket/' . $itemName;
        $target = $basketItem->getPath();


/*        $this->helper->prent($origin);
        $this->helper->prent($target);*/

        //Restoring item to it`s previous location
        $this->fileSystem->move($origin, $target);


        //Deleting item from basket table
        $this->entityManager->remove($basketItem);
        $this->entityManager->flush();

        return new Response(
            'restoreFromBasket',
            Response::HTTP_OK
        );
    }



    private function getStorageFiles(string $storagePath, $type = false) {
        $arrFileExtensions = $this->fileSystem->getFileTypeExtensions($type);

        $finder = new Finder();
        $finder->files()->in($storagePath);
        $arrResult = [];

        if ($finder->hasResults()) {
            $counter = 0;
            foreach ($finder as $file) {
                $fileName = $file->getRelativePathname();
                list($name, $extension) = explode('.', $fileName);
                $fileType = $this->fileSystem->getFileType($extension);

                if (!empty($arrFileExtensions)) {
                    //selected type of files
                    if (in_array($extension, $arrFileExtensions)) {
                        $arrResult[$counter]['NAME'] = $fileName;
                        $arrResult[$counter]['EXTENSION'] = $extension;
                        $arrResult[$counter]['FILE_TYPE'] = $fileType;
                        $arrResult[$counter]['FILE_STYLES'] = $this->fileSystem->getFileStyles($type);
                    }
                } else {
                    //all files
                    $arrResult[$counter]['NAME'] = $fileName;
                    $arrResult[$counter]['EXTENSION'] = $extension;
                    $arrResult[$counter]['FILE_TYPE'] = $fileType;
                    $arrResult[$counter]['FILE_STYLES'] = $this->fileSystem->getFileStyles($fileType);
                }

                $counter++;
            }
        }

        return $arrResult;
    }


    private function getStorageDirectories(string $storagePath)
    {
        $finder = new Finder();
        $finder->directories()->in($storagePath);
        $arrResult = [];

        if ($finder->hasResults()) {
            foreach ($finder as $directory) {
                if ($directoryName = $directory->getRelativePathname()) {
                    $arrResult[] = $directoryName;
                }
            }
        }

        return $arrResult;
    }


}
