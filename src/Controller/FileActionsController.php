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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\GlobalFunctions\Helper;



class FileActionsController extends AbstractController
{
    public $fileSystem;
    public $coreFileSystem;

    /** @var EntityManagerInterface */
    private $entityManager;

    private $security;
    private $userId;

    public function __construct(
        Services\FileSystemService $fileSystem,
        EntityManagerInterface $entityManager,
        Security $security
    )
    {
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->coreFileSystem = new Filesystem();
        $this->security = $security;
        $this->userId = $this->fileSystem->getUserId();
    }

    /**
     * @Route("/", name="showStartPage")
     */
    public function renderMainPage()
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userId = $this->security->getUser()->getId();
            return $this->redirect("/get-files/user_$userId-disk");
        } else {
            return $this->render('start.html.twig', []);
        }
    }

    /**
     * @Route("/get-files/{path}/{fileType}", name="getFiles")
     */
    public function getFiles($path, $fileType = false)
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('showStartPage');
        }

        $storagePath = Helper::getFileStoragePath($path);


        $response = [
            'current_path' => $path,
            'canonical_current_path' => str_replace ( '//', '/', $storagePath)
        ];


        $arrDirectories = $this->getStorageDirectories($storagePath);
        $arrFiles = $this->getStorageFiles($storagePath, $fileType);


        $breadcrumbs = $this->makeBreadcrumbs($storagePath);

        $response['folders'] = $arrDirectories;
        $response['files'] = $arrFiles;
        $response['breadcrumbs'] = $breadcrumbs;
        $response['breadcrumbs_cnt'] = count($breadcrumbs);


        return $this->render('user-data.html.twig', [
            'response' => $response,
        ]);
    }

    public function getStorageFiles(string $storagePath, $type = false) {
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


                if ($fileName && !strpos($fileName, '/')) {
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
                }

                $counter++;
            }
        }


        return $arrResult;
    }

    public function getStorageDirectories(string $storagePath)
    {
        $finder = new Finder();
        $finder->directories()->in($storagePath);
        $arrResult = [];

        if ($finder->hasResults()) {
            foreach ($finder as $directory) {
                $directoryName = $directory->getRelativePathname();

                if ($directoryName && !strpos($directoryName, '/')) {
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

    /**Сборка хлебной крошки
     *
     * @param $storagePath
     * @return array
     */
    private function makeBreadcrumbs($storagePath) {
        $arLink = explode('/', $storagePath);

        $arrLinkForExclude = [
            '',  'var', 'www', 'openDrive',
            'public', 'storage', 'user_'.$this->userId, 'disk'
        ];

        $userDiskRoot = "/get-files/user_$this->userId-disk";

        $arBreadcrumbs = [];
        $arBreadcrumbs[$userDiskRoot] = ucfirst($this->getFolderName($userDiskRoot));
        $breadcrumbElement = '';

        foreach ($arLink as $link) {
            if (in_array($link, $arrLinkForExclude)) continue;
            $breadcrumbElement .= '-'.$link;
            $breadcrumbElement = trim($breadcrumbElement, '-');
            $link = $userDiskRoot .'-'. $breadcrumbElement;
            $arBreadcrumbs[$link] = ucfirst($this->getFolderName($link));
        }


        if ($_SERVER['REQUEST_URI'] == $userDiskRoot) {
            $arBreadcrumbs = [];
        }


        return $arBreadcrumbs;
    }

    private function getFolderName($folderLink) {
        $arLink = explode('-', $folderLink);
        $folderName = end($arLink);
        return $folderName;
    }

}
