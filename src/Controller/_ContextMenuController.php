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



/*    public function __construct(Services\FileSystemService $fileSystem, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
    }*/


    /**
     * @Route("/file-download111/{linkToFile}")
     */
/*    public function downloadFile($linkToFile)
    {
        $linkToFile = $_SERVER['DOCUMENT_ROOT']
            . $this->fileSystem::STORAGE_PATH
            . $this->fileSystem::BASE_PATH
            . $linkToFile;

        return $this->file($linkToFile);
    }*/

    /**
     * @Route("/file-delete111/{link}")
     */
/*    public function fileDelete($link)
    {
        $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $link;
        $this->fileSystem->addToBasket($link);


        return new Response(
            Response::HTTP_OK
        );
    }*/



    /**
     * @Route("/file-rename111/")
     */
/*    public function rename()
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
    }*/



    /**
     * @Route("/file-move111/{link}")
     */
/*    public function filemove($link)
    {
        $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem::BASE_PATH . $link;
        $this->fileSystem->addToBasket($link);


        return new Response(
            Response::HTTP_OK
        );
    }*/



}
