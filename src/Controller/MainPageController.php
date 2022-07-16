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


use Symfony\Component\Finder\Finder;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;



class MainPageController extends AbstractController
{

    public $helper;
    public $fileSystem;


    /** @var EntityManagerInterface */
    private $entityManager;



    public function __construct(
        Services\HelperService $helper,
        Services\FileSystemService $fileSystem,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
    }



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
                $arrFileExtensions = $this->fileSystem::FILE_EXTENSIONS['IMAGES'];
                break;
            case "audio":
                $arrFileExtensions = $this->fileSystem::FILE_EXTENSIONS['AUDIO'];
                break;
            case "video":
                $arrFileExtensions = $this->fileSystem::FILE_EXTENSIONS['VIDEO'];
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


    /**
     * @Route("/get-user-disk-info")
     */
    public function getUserDiskInfo()
    {
        //TODO передавать ID диска конкретного пользователя


        $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/';
        $directory = new RecursiveDirectoryIterator($storagePath);
        $directory->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        $files = new RecursiveIteratorIterator(
            $directory,
            RecursiveIteratorIterator::SELF_FIRST
        );

        $result = [];
        $totalUsedSize = 0;
        $spaceUsedByImages = 0;
        $spaceUsedByMedia = 0;
        $spaceUsedByDocuments = 0;
        $spaceUsedByOtherFiles = 0;

        foreach ($files as $file) {
            if (!$file->isDir()) {

                $fileName = $file->getBasename();
                $fileSizeInBytes = $file->getSize();
                $fileExtension = $file->getExtension();
                $fileType = $this->fileSystem->getFileType($fileExtension);

                $totalUsedSize += $fileSizeInBytes;


                switch ($fileType) {
                    case 'IMAGES':
                        $spaceUsedByImages += $fileSizeInBytes;
                        break;
                    case 'AUDIO':
                        $spaceUsedByMedia += $fileSizeInBytes;
                        break;
                    case 'VIDEO':
                        $spaceUsedByMedia += $fileSizeInBytes;
                        break;
                    case 'DOCUMENTS':
                        $spaceUsedByDocuments += $fileSizeInBytes;
                        break;
                    default:
                        $spaceUsedByOtherFiles += $fileSizeInBytes;
                }



                $result['FILES'][$fileName]['NAME'] = $fileName;
                $result['FILES'][$fileName]['EXTENSION'] = $fileExtension;
                $result['FILES'][$fileName]['FILE_TYPE'] = $fileType;
                $result['FILES'][$fileName]['SIZE_IN_BYTES'] = $fileSizeInBytes;
                $result['FILES'][$fileName]['SIZE_FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($fileSizeInBytes);
            }
        }

        $result['TOTAL_SIZE']['IN_BYTES'] = $totalUsedSize;
        $result['TOTAL_SIZE']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($totalUsedSize);

        $result['SIZE_OF_IMAGES']['IN_BYTES'] = $spaceUsedByImages;
        $result['SIZE_OF_IMAGES']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByImages);
        $result['SIZE_OF_IMAGES']['PERCENTAGE_OF_TOTAL'] = ($spaceUsedByImages / $totalUsedSize) * 100;

        $result['SIZE_OF_MEDIA']['IN_BYTES'] = $spaceUsedByMedia;
        $result['SIZE_OF_MEDIA']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByMedia);
        $result['SIZE_OF_MEDIA']['PERCENTAGE_OF_TOTAL'] = ($spaceUsedByMedia / $totalUsedSize) * 100;

        $result['SIZE_OF_DOCUMENTS']['IN_BYTES'] = $spaceUsedByDocuments;
        $result['SIZE_OF_DOCUMENTS']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByDocuments);
        $result['SIZE_OF_DOCUMENTS']['PERCENTAGE_OF_TOTAL'] = ($spaceUsedByDocuments / $totalUsedSize) * 100;

        $result['SIZE_OF_OTHER_FILES']['IN_BYTES'] = $spaceUsedByOtherFiles;
        $result['SIZE_OF_OTHER_FILES']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByOtherFiles);
        $result['SIZE_OF_OTHER_FILES']['PERCENTAGE_OF_TOTAL'] = ($spaceUsedByOtherFiles / $totalUsedSize) * 100;


        $this->helper->prent($result);


        return new Response(
            'getUserDiskInfo',
            Response::HTTP_OK
        );
    }







}
