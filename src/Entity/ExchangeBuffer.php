<?php

namespace App\Entity;

use App\Repository\ExchangeBufferRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeBufferRepository::class)]
class ExchangeBuffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $user_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $action;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $file_path;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $file;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUserId(): ?int
    {
        return $this->user_id;
    }


    public function setUserId(?int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }


    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }


    public function getFile(): ?string
    {
        return $this->file;
    }


    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }


}
