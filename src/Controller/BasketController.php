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
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Basket;

use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;


class BasketController extends AbstractController
{
    private $fileSystem;
    private $entityManager;
    private $coreFileSystem;
    private $exchangeBuffer;
    private $fileActionsController;

    private $userBasePath;
    private $userId;
    private $userBasketPath;

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

        $this->userBasePath = $this->fileSystem->getUserBasePath();
        $this->userId = $this->fileSystem->getUserId();
        $this->userBasketPath = $this->fileSystem->getUserBasketPath();
    }


    /**
     * @Route("/file-delete/", name="fileDelete")
     */
    public function fileDelete(Request $request)
    {
        $filePath= $request->get('filePath');
        $fileName = $request->get('fileName');
        $basketItemSQL = $this->getBasketItemByName($fileName);

        $fileLink = $filePath.'/'.$fileName;

        //adding to the basket or deleteting completely
        if ($request->get('deleteCompletely') == 'Y') {
            $basketLink = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->userBasketPath . $fileName;
            $this->fileSystem->remove($basketLink);
            //delete from database
            $this->entityManager->remove($basketItemSQL);
            $this->entityManager->flush();
        } elseif ($basketItemSQL) {
            //delete if this file exist in the database
            $this->fileSystem->remove($fileLink);
        } else {
            $this->addFileToBasket($fileLink);
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
        $basket = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->userBasketPath;
        $arrDirectories = $this->fileActionsController->getStorageDirectories($basket);
        $arrFiles = $this->fileActionsController->getStorageFiles($basket);

        $response = [
            'folders' => $arrDirectories,
            'files' => $arrFiles,
            'current_path' => '/basket/',
            'canonical_current_path' => str_replace ( '//', '/', $basket),
            'user_id' => $this->userId

        ];

        return $this->render('basket.html.twig', [
            'response' => $response,
        ]);
    }


    /**
     * @Route("/clean-basket/", name="cleanBasket")
     */
    public function cleanBasket()
    {
        $basketPath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->userBasketPath;

        $basket = scandir($basketPath);
        unset($basket[0], $basket[1]);

        if (count($basket) > 0) {
            $directoryIterator = new RecursiveDirectoryIterator($basketPath, FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($recursiveIterator as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
        }

        $this->deleteAllUserBasketRows($this->userId);
        return $this->redirectToRoute('getBasket');
    }


    private function deleteAllUserBasketRows($userId) {
        $basketRepository = $this->entityManager->getRepository(Basket::class);
        $basketItems = $basketRepository->findBy(['user_id' => $userId]);
        if ($basketItems) {
            foreach ($basketItems as $item) {
                $this->entityManager->remove($item);
            }

            $this->entityManager->flush();
        }
    }


    private function addFileToBasket($origin)
    {
        $arrOrigin = explode('/', $origin);
        $fileName = end($arrOrigin);

        $target = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->userBasketPath . $fileName;
        $this->fileSystem->move($origin, $target);

        $basketItem = $this->getBasketItemByName($fileName);


        if (!$basketItem) {
            $basket = new Basket();
            $basket->setPath($origin);
            $basket->setItem($fileName);
            $basket->setUserId($this->userId);

            $this->entityManager->persist($basket);
            $this->entityManager->flush();
        }
    }

    private function restoreFile($fileName)
    {
        $basketItem = $this->getBasketItemByName($fileName);

        $origin = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->userBasketPath . $fileName;
        $target = $basketItem->getPath();
        $this->fileSystem->move($origin, $target);
        $this->entityManager->remove($basketItem);
        $this->entityManager->flush();
    }

    private function getBasketItemByName($fileName)
    {
        $basketRepository = $this->entityManager->getRepository(Basket::class);
        $basketItem = $basketRepository->findOneBy([
            'item' => $fileName,
            'user_id' => $this->userId
        ]);

        return $basketItem;
    }
}