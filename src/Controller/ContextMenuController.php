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
     * @Route("/file-copy/", name="fileCopy")
     */
    public function copyFile(Request $request)
    {
        //TODO добавить пользователя после создания регистрации, авторизации
        $userId = 1;
        $action = $request->get('action');
        $filePath= $request->get('filePath');
        $fileName = $request->get('fileName');

        $this->exchangeBuffer->setBufferAction($userId, $action, $filePath, $fileName);

        $jsonData = [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'action' => $action
        ];


        return new JsonResponse($jsonData);
    }


    /**
     * @Route("/file-paste/", name="filePaste")
     */
    public function pasteFile(Request $request)
    {

        //TODO copy, move в ту же папку баг


        $userId = 1;
        $copiedFile = $this->exchangeBuffer->getBufferAction($userId);
        $origin = $copiedFile->getFilePath() . '/' . $copiedFile->getFile();
        $target = $request->get('filePath') . '/' . $copiedFile->getFile();
        $action = $copiedFile->getAction();


        if ($action == 'move') {
            $this->fileSystem->move($origin, $target);
        } elseif($action == 'copy') {
            $this->fileSystem->copy($origin, $target);
        }

        $this->exchangeBuffer->deleteBufferAction($userId, $action);

        $jsonData = [
            'origin' => $origin,
            'target' => $target,
            'action' => $action
        ];


        return new JsonResponse($jsonData);
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
     * @Route("/file-delete/", name="fileDelete")
     */
    public function fileDelete(Request $request)
    {
        $user_id = 1;

        $fileName = $request->get('fileName');

        //adding to the basket or deleteting completely
        if ($request->get('deleteCompletely') == 'Y') {
            $link = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . 'basket_user_'.$user_id .'/'. $fileName;
            $this->fileSystem->remove($link);
        } else {
            $filePath= $request->get('filePath');
            $link = $filePath.'/'.$fileName;
            $this->fileSystem->addToBasket($link);
        }


        return new Response(Response::HTTP_OK);
    }


    /**
     * @Route("/folder-delete/", name="folderDelete")
     */
    public function folderDelete(Request $request)
    {
        $folderPath = $request->get('folderPath');
        $folderName = $request->get('folderName');
        $link = $folderPath . '/' . $folderName;
        $this->fileSystem->remove($link);
        return new Response(Response::HTTP_OK);
    }


    /**
     * @Route("/folder-create/", name="folderCreate")
     */
    public function folderCreate(Request $request)
    {
        $folderPath = $request->request->get('FOLDER_PATH');
        $folderName = $request->request->get('FOLDER_NAME');
        $link = $folderPath . '/' . $folderName;
        $this->coreFileSystem->mkdir($link);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return new Response(Response::HTTP_OK);

    }


    /**
     * @Route("/file-restore/", name="fileRestore")
     */
    public function restoreFromBasket(Request $request)
    {
        $fileName = $request->get('fileName');
        $this->fileSystem->restoreFromBasket($fileName);
        return new Response(Response::HTTP_OK);
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


    /**
     * @Route("/get-exchange-buffer")
     */
    public function getExchangeBuffer(Request $request) {

        $userId = 1;
        $bufferRow = $this->exchangeBuffer->getBufferAction($userId);

        if ($bufferRow) {
            $jsonResponse = [
                'status' => 'BUFFER',
                'action' => $bufferRow->getAction(),
                'file_path' => $bufferRow->getFilePath(),
                'file' => $bufferRow->getFile()
            ];
        } else {
            $jsonResponse = [
                'status' => 'BUFFER_NONE',
            ];
        }


        return new JsonResponse($jsonResponse);
    }





}