<?php

namespace App\Entity;

use App\Repository\TinyUrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TinyUrlRepository::class)
 */
class TinyUrl
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=9, unique=true)
     */
    private $short;

    /**
     * @Assert\NotBlank
     * @Assert\Url()
     * @Assert\Length(min = 1, max = 255)
     * @ORM\Column(type="string", length=255)
     */
    private $full;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(string $short): self
    {
        $this->short = $short;

        return $this;
    }

    public function getFull(): ?string
    {
        return $this->full;
    }

    public function setFull(string $full): self
    {
        $this->full = $full;

        return $this;
    }
}
