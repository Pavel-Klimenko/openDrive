<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/22
 * Time: 23:35
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller;

use Symfony\Component\Finder\Finder;



class MainPageController extends AbstractController
{
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

        $storagePath = $_SERVER['DOCUMENT_ROOT'].'/storage/';

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
            'There are no jobs in the database',
            Response::HTTP_OK
        );


    }


    /**
     * @Route("/get-images")
     */
    public function getImages()
    {

        //TODO сделать эту функцию универсальной для всех файлов


        //TODO все это в один массив запихнуть
        $arrImgExtensions = ['jpg', 'png', 'jpeg', 'webp'];
        $arrAudioExtensions = ['mp3', 'aac', 'wav', 'flac'];
        $arrVideoExtensions = ['mp4', 'avi', 'mov', 'mpeg'];



        $storagePath = $_SERVER['DOCUMENT_ROOT'].'/storage/';
        $finder = new Finder();
        $finder->files()->in($storagePath);
        $arrResult = [];



        $arrFileExtensions = $arrVideoExtensions;


        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $fileName = $file->getRelativePathname();


                //var_dump($fileName);

                list($name, $extension) = explode('.', $fileName);
/*
                var_dump($name);
                var_dump($extension);*/


                if (in_array($extension, $arrFileExtensions)) {
                    $arrResult[] = $fileName;
                }


/*                if (str_contains($entityName, '.')) {
                    $arrFiles[] = $entityName;
                } else {
                    $arrDirectories[] = $entityName;
                }*/

            }
        }


        var_dump($arrResult);




        return new Response(
            'There are no jobs in the database',
            Response::HTTP_OK
        );
    }


}