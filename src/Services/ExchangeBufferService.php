<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ExchangeBuffer;

class ExchangeBufferService {

    public $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function getBufferAction($userId) {
        $exchangeBuffer = $this->entityManager->getRepository(ExchangeBuffer::class);
        $bufferAction = $exchangeBuffer->findBy(
            ['user_id' => $userId, 'action' => ['copy', 'move']]
        );

        return $bufferAction[0];
    }


    public function setBufferAction($userId, $action, $filePath, $fileName) {
        //TODO брать userID глобально

        $buffer = new ExchangeBuffer();
        $buffer->setUserId($userId);
        $buffer->setAction($action);
        $buffer->setFilePath($filePath);
        $buffer->setFile($fileName);

        $this->entityManager->persist($buffer);
        $this->entityManager->flush();
    }


    public function deleteBufferAction($userId, $action) {
        //TODO брать userID глобально
        $exchangeBuffer = $this->entityManager->getRepository(ExchangeBuffer::class);
        $bufferAction = $exchangeBuffer->findBy([
            'user_id' => $userId,
            'action' => $action,
        ]);

        $this->entityManager->remove($bufferAction[0]);
        $this->entityManager->flush();
    }

}