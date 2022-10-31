<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/22
 * Time: 23:35
 */

namespace App\Controller;


use App\Entity\Basket;
use App\Entity\ExchangeBuffer;
use App\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\GlobalFunctions\Helper;

use Symfony\Component\Finder\Finder;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;



class FileActionsController extends AbstractController
{

    public $fileSystem;
    public $coreFileSystem;



    /** @var EntityManagerInterface */
    private $entityManager;



    public function __construct(
        Services\FileSystemService $fileSystem,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->coreFileSystem = new Filesystem();
    }




    /*TODO прописать нормальные роуты*/
    /**
     * @Route("/", name="showStartPage")
     */
    public function renderMainPage()
    {
        return $this->render('start.html.twig', []);
    }



    /*TODO прописать нормальные роуты*/

    /**
     * @Route("/get-files/{path}/{fileType}", name="getFiles")
     */
    public function getFiles($path, $fileType = false)
    {

        if (str_contains($path, '-')) {
            $arLink = explode('-', $path);
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . implode('/', $arLink);
        } else {
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH .$path;
        }


        $arrDirectories = $this->getStorageDirectories($storagePath);
        $arrFiles = $this->getStorageFiles($storagePath, $fileType);

        $response = [
            'folders' => $arrDirectories,
            'files' => $arrFiles,
            'current_path' => $path,
            'canonical_current_path' => str_replace ( '//', '/', $storagePath)
        ];


        return $this->render('user-data.html.twig', [
            'response' => $response,
        ]);

    }


    /**
     * @Route("/basket/", name="getBasket")
     */
    public function getBasket()
    {
        $userId = 1;
        $basket = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . 'basket_user_' . $userId;


        $arrDirectories = $this->getStorageDirectories($basket);
        $arrFiles = $this->getStorageFiles($basket);

        $response = [
            'folders' => $arrDirectories,
            'files' => $arrFiles,
            'current_path' => '/basket/',
            'canonical_current_path' => str_replace ( '//', '/', $basket)

        ];

        return $this->render('basket.html.twig', [
            'response' => $response,
        ]);
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


    private function getStorageFiles(string $storagePath, $type = false) {
        $arrFileExtensions = $this->fileSystem->getFileTypeExtensions($type);

        $finder = new Finder();
        $finder->files()->in($storagePath);
        $arrResult = [];

        if ($finder->hasResults()) {
            $counter = 0;
            foreach ($finder as $file) {
                $fileName = $file->getRelativePathname();
                $fileUrl = stristr($file->getPathName(), '/storage/');
                $fileSize = $this->fileSystem->FileSizeConvert($file->getSize());


                list($name, $extension) = explode('.', $fileName);
                $fileType = $this->fileSystem->getFileType($extension);

                if (!empty($arrFileExtensions)) {
                    //selected type of files
                    if (in_array($extension, $arrFileExtensions)) {
                        $arrResult[$counter]['FILE_URL'] = $fileUrl;
                        $arrResult[$counter]['NAME'] = $fileName;
                        $arrResult[$counter]['EXTENSION'] = $extension;
                        $arrResult[$counter]['FILE_TYPE'] = $fileType;
                        $arrResult[$counter]['FILE_STYLES'] = $this->fileSystem->getFileStyles($type);
                        $arrResult[$counter]['FILE_SIZE'] = $fileSize;
                    }
                } else {
                    //all files
                    $arrResult[$counter]['FILE_URL'] = $fileUrl;
                    $arrResult[$counter]['NAME'] = $fileName;
                    $arrResult[$counter]['EXTENSION'] = $extension;
                    $arrResult[$counter]['FILE_TYPE'] = $fileType;
                    $arrResult[$counter]['FILE_STYLES'] = $this->fileSystem->getFileStyles($fileType);
                    $arrResult[$counter]['FILE_SIZE'] = $fileSize;
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


    /**
     * @Route("/file-upload", name="fileUpload")
     */
    public function fileUpload(Request $request)
    {
        $newFileObject = $request->files->get('new_file');
        $newFileName = $request->files->get('new_file')->getClientOriginalName();
        $currentPath = $request->request->get('current_path') . '/';
        $newFileObject->move($currentPath, $newFileName);
        header("Location: ".$_SERVER['HTTP_REFERER']);
        return new Response('fileUpload', Response::HTTP_OK);
    }

}