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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;



class ContextMenuController extends AbstractController
{

    public $fileSystem;


    /** @var EntityManagerInterface */
    private $entityManager;



    public function __construct(Services\FileSystemService $fileSystem, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
    }


    /**
     * @Route("/file-download/{linkToFile}")
     */
    public function downloadFile($linkToFile)
    {
        $linkToFile = $_SERVER['DOCUMENT_ROOT']
            . $this->fileSystem::STORAGE_PATH
            . $this->fileSystem::BASE_PATH
            . $linkToFile;

        return $this->file($linkToFile);
    }

    /**
     * @Route("/file-delete/{link}")
     */
    public function fileDelete($link)
    {
        $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $link;
        $this->fileSystem->addToBasket($link);


        return new Response(
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/file-restore/{itemName}")
     */
    public function restoreFromBasket($itemName)
    {
        $this->fileSystem->restoreFromBasket($itemName);

        return new Response(
            Response::HTTP_OK
        );
    }



    /**
     * @Route("/file-rename/")
     */
    public function rename()
    {

        $oldName = 'subDirectory1';
        $newName = 'subDirectoryNEW';

        $oldName = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $oldName;
        $newName = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $newName;


        var_dump($oldName);
        var_dump($newName);

        $this->fileSystem->rename($oldName, $newName);

        return new Response(
            Response::HTTP_OK
        );
    }



    /**
     * @Route("/file-move/{link}")
     */
    public function filemove($link)
    {
        $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $link;
        $this->fileSystem->addToBasket($link);


        return new Response(
            Response::HTTP_OK
        );
    }




    /**
     * @Route("/file-properties/{linkToFile}")
     */
    public function getFileProps($linkToFile)
    {
        $linkToFile = $_SERVER['DOCUMENT_ROOT']
            . $this->fileSystem::STORAGE_PATH
            . $this->fileSystem::BASE_PATH
            . $linkToFile;


        $fileProps = stat($linkToFile);

        $arLink = explode('/', $linkToFile);
        $file = end($arLink);
        $arFile = explode('.', $file);
        $fileExtension = end($arFile);


        $response = [
          'file-path' => $linkToFile,
          'file-extension' =>$fileExtension,
          'size' => $this->fileSystem->FileSizeConvert($fileProps['size']),
          'last-modified' => date('d.m.Y h:i:s A', $fileProps['ctime']),
        ];


        var_dump($response);

        return new Response(
            'showFolder',
            Response::HTTP_OK
        );
    }






}
