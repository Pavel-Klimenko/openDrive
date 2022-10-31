<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $user_id;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $item;



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


    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getPath(): ?string
    {
        return $this->path;
    }


    public function setPath(?string $path): self
    {
        $this->path= $path;
        return $this;
    }


    public function getItem(): ?string
    {
        return $this->item;
    }


    public function setItem(?string $item): self
    {
        $this->item = $item;
        return $this;
    }

}
