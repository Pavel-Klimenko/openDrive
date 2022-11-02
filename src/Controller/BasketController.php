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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Basket;
use App\Controller\FileActionsController;


class BasketController extends AbstractController
{
    private $fileSystem;
    private $entityManager;
    private $coreFileSystem;
    private $exchangeBuffer;
    private $fileActionsController;

    public function __construct(
        Services\FileSystemService $fileSystem,
        Services\ExchangeBufferService $exchangeBuffer,
        EntityManagerInterface $entityManager,
        FileActionsController $fileActionsController
    )
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->coreFileSystem = new Filesystem();
        $this->exchangeBuffer = $exchangeBuffer;
        $this->fileActionsController = $fileActionsController;
    }


    /**
     * @Route("/file-delete/", name="fileDelete")
     */
    public function fileDelete(Request $request)
    {

        $userBasePath = $this->fileSystem->getUserBasePath();
        $fileName = $request->get('fileName');

        //adding to the basket or deleteting completely
        if ($request->get('deleteCompletely') == 'Y') {
            $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $userBasePath . $fileName;
            $this->fileSystem->remove($link);
        } else {
            $filePath= $request->get('filePath');
            $link = $filePath.'/'.$fileName;
            $this->addFileToBasket($link);
        }


        return new Response(Response::HTTP_OK);
    }


    /**
     * @Route("/file-restore/", name="fileRestore")
     */
    public function restoreFromBasket(Request $request)
    {
        $fileName = $request->get('fileName');
        $this->restoreFile($fileName);
        return new Response(Response::HTTP_OK);
    }


    /**
     * @Route("/basket/", name="getBasket")
     */
    public function getBasket()
    {
        $userId = $this->fileSystem->getUserId();
        $userBasketPath = $this->fileSystem->getUserBasketPath();
        $basket = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $userBasketPath;


        //var_dump($basket);

        $arrDirectories = $this->fileActionsController->getStorageDirectories($basket);
        $arrFiles = $this->fileActionsController->getStorageFiles($basket);

        $response = [
            'folders' => $arrDirectories,
            'files' => $arrFiles,
            'current_path' => '/basket/',
            'canonical_current_path' => str_replace ( '//', '/', $basket),
            'user_id' => $userId

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


    private function addFileToBasket($origin)
    {
        $userBasketPath = $this->fileSystem->getUserBasketPath();

        $arrOrigin = explode('/', $origin);
        $fileName = end($arrOrigin);

        $target = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $userBasketPath . $fileName;
        $type = (is_dir($_SERVER['DOCUMENT_ROOT'] . $origin)) ? 'folder' : 'file';
        $user_id = $this->fileSystem->getUserId();
        $this->fileSystem->move($origin, $target);

        $basket = new Basket();
        $basket->setType($type);
        $basket->setPath($origin);
        $basket->setItem($fileName);
        $basket->setUserId($user_id);

        $this->entityManager->persist($basket);
        $this->entityManager->flush();
    }


    private function restoreFile($fileName)
    {

        $userBasketPath = $this->fileSystem->getUserBasketPath();
        $user_id = $this->fileSystem->getUserId();

        $basketRepository = $this->entityManager->getRepository(Basket::class);
        $basketItem = $basketRepository->findOneBy([
            'item' => $fileName,
            'user_id' => $user_id
        ]);


        $origin = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $userBasketPath . $fileName;
        $target = $basketItem->getPath();
        $this->fileSystem->move($origin, $target);
        $this->entityManager->remove($basketItem);
        $this->entityManager->flush();
    }

}