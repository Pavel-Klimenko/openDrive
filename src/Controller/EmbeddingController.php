<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/22
 * Time: 23:35
 */

namespace App\Controller;

use App\GlobalFunctions\Helper;
use App\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class EmbeddingController extends AbstractController
{

    public $fileSystem;

    public function __construct(
        Services\FileSystemService $fileSystem
    )
    {
        $this->fileSystem = $fileSystem;
    }


    public function getUserDiskInfo()
    {
        $storagePath = $_SERVER['DOCUMENT_ROOT'] . $this->fileSystem::STORAGE_PATH . $this->fileSystem->getUserBasePath();


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
        $spaceUsedByArchives = 0;
        $spaceUsedByOtherFiles = 0;


        $imagesQuantity = 0;
        $mediaQuantity = 0;
        $documentsQuantity = 0;
        $archivesQuantity = 0;
        $otherFilesQuantity = 0;


        foreach ($files as $file) {
            if (!$file->isDir()) {

                $fileName = $file->getBasename();
                $fileSizeInBytes = $file->getSize();
                $fileExtension = $file->getExtension();
                $fileType = $this->fileSystem->getFileType($fileExtension);

                $totalUsedSize += $fileSizeInBytes;


                switch ($fileType) {
                    case 'img':
                        $spaceUsedByImages += $fileSizeInBytes;
                        $imagesQuantity += 1;
                        break;
                    case 'audio':
                        $spaceUsedByMedia += $fileSizeInBytes;
                        $mediaQuantity += 1;
                        break;
                    case 'video':
                        $spaceUsedByMedia += $fileSizeInBytes;
                        $mediaQuantity += 1;
                        break;
                    case 'documents':
                        $spaceUsedByDocuments += $fileSizeInBytes;
                        $documentsQuantity += 1;
                        break;
                    case 'archives':
                        $spaceUsedByArchives += $fileSizeInBytes;
                        $archivesQuantity += 1;
                        break;
                    default:
                        $spaceUsedByOtherFiles += $fileSizeInBytes;
                        $otherFilesQuantity += 1;
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
        $result['SIZE_OF_IMAGES']['FILES_QUANTITY'] = $imagesQuantity;

        $result['SIZE_OF_IMAGES']['PERCENTAGE_OF_TOTAL'] = Helper::getPercentOfTotal($spaceUsedByImages, $totalUsedSize);

        $result['SIZE_OF_MEDIA']['IN_BYTES'] = $spaceUsedByMedia;
        $result['SIZE_OF_MEDIA']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByMedia);
        $result['SIZE_OF_MEDIA']['FILES_QUANTITY'] = $mediaQuantity;
        $result['SIZE_OF_MEDIA']['PERCENTAGE_OF_TOTAL'] = Helper::getPercentOfTotal($spaceUsedByMedia, $totalUsedSize);

        $result['SIZE_OF_DOCUMENTS']['IN_BYTES'] = $spaceUsedByDocuments;
        $result['SIZE_OF_DOCUMENTS']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByDocuments);
        $result['SIZE_OF_DOCUMENTS']['FILES_QUANTITY'] = $documentsQuantity;
        $result['SIZE_OF_DOCUMENTS']['PERCENTAGE_OF_TOTAL'] = Helper::getPercentOfTotal($spaceUsedByDocuments, $totalUsedSize);

        $result['SIZE_OF_ARCHIVES']['IN_BYTES'] = $spaceUsedByArchives;
        $result['SIZE_OF_ARCHIVES']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByArchives);
        $result['SIZE_OF_ARCHIVES']['FILES_QUANTITY'] = $archivesQuantity;
        $result['SIZE_OF_ARCHIVES']['PERCENTAGE_OF_TOTAL'] = Helper::getPercentOfTotal($spaceUsedByArchives, $totalUsedSize);

        $result['SIZE_OF_OTHER_FILES']['IN_BYTES'] = $spaceUsedByOtherFiles;
        $result['SIZE_OF_OTHER_FILES']['FOR_DISPLAY'] = $this->fileSystem->FileSizeConvert($spaceUsedByOtherFiles);
        $result['SIZE_OF_OTHER_FILES']['FILES_QUANTITY'] = $otherFilesQuantity;
        $result['SIZE_OF_OTHER_FILES']['PERCENTAGE_OF_TOTAL'] = Helper::getPercentOfTotal($spaceUsedByOtherFiles, $totalUsedSize);

        //TODO pass custom max. disk size

        return $this->render('fragments/disk-info.html.twig', [
            'diskInfo' => $result,
        ]);
    }


}
