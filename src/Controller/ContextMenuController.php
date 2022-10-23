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


class ContextMenuController extends AbstractController
{

    private $fileSystem;

    private $entityManager;

    private $coreFileSystem;

    private $exchangeBuffer;



    public function __construct(
        Services\FileSystemService $fileSystem,
        Services\ExchangeBufferService $exchangeBuffer,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;

        $this->fileSystem = $fileSystem;

        $this->coreFileSystem = new Filesystem();

        $this->exchangeBuffer = $exchangeBuffer;
    }


    /**
     * @Route("/file-copy/{path}/", name="fileCopy")
     */
    public function copyFile($path)
    {
        //TODO вынести в метод
        if (str_contains($path, '-')) {
            $arLink = explode('-', $path);
            $fileName = end($arLink);
            array_pop($arLink);

            $currentPath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . implode('/', $arLink);
        } else {
            $currentPath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH .$path;
        }

        var_dump($currentPath);

        //TODO добавить пользователя после создания регистрации, авторизации
        $userId = 1;
        $isMove = true;

        //копировать или переместить?
        $actionType = ($isMove) ? 'move' : 'copy';

        /*       $bufferAction = $this->getBufferAction($userId, 'copy');
                var_dump($bufferAction);*/

        $this->exchangeBuffer->setBufferAction($userId, $actionType, $currentPath, $fileName);
        //return $this->getFiles('/user_files');

        return new Response(
            'copy',
            Response::HTTP_OK
        );
    }


    /**
     * @Route("/file-paste/{currentPath}", name="filePaste")
     */
    public function pasteFile($currentPath)
    {

        //TODO вынести в метод
        if (str_contains($currentPath, '-')) {
            $arLink = explode('-', $currentPath);
            array_pop($arLink);
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . implode('/', $arLink);
        } else {
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH .$currentPath;
        }



        $userId = 1;
        $copiedFile = $this->exchangeBuffer->getBufferAction($userId);
        $origin = $copiedFile->getFilePath() . '/' . $copiedFile->getFile();
        $target = $targetPath . '/' . $copiedFile->getFile();


        var_dump($copiedFile);


        $action = $copiedFile->getAction();

        var_dump($copiedFile->getAction());
        var_dump($origin);
        var_dump($target);


        if ($action == 'move') {
            $this->fileSystem->move($origin, $target);
        } elseif($action == 'copy') {
            $this->fileSystem->copy($origin, $target);
        }


        $this->exchangeBuffer->deleteBufferAction($userId, $action);



        return new Response(
            'paste',
            Response::HTTP_OK
        );

    }


    /**
     * @Route("/file-rename", name="fileRename")
     */
    public function fileRename(Request $request)
    {
        $filePath = $request->request->get('FILE_PATH');
        $oldName = $request->request->get('FILE_OLD_NAME');
        $newName = $request->request->get('FILE_NEW_MAME');

        $oldName = $filePath . $oldName;
        $newName = $filePath . $newName;

        $this->coreFileSystem->rename($oldName, $newName);
        header('Location: ' . $_SERVER['HTTP_REFERER']);

        return new Response(
            'fileRename',
            Response::HTTP_OK
        );
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