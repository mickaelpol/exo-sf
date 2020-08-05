<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRepository")
 */
class Search
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     * @Assert\Length(min="3", max="255")
     * @var string
     */
    private $search;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city_lat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city_lon;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getCityId(): ?string
    {
        return $this->city_id;
    }

    public function setCityId(?string $city_id): self
    {
        $this->city_id = $city_id;

        return $this;
    }

    public function getCityLat(): ?string
    {
        return $this->city_lat;
    }

    public function setCityLat(?string $city_lat): self
    {
        $this->city_lat = $city_lat;

        return $this;
    }

    public function getCityLon(): ?string
    {
        return $this->city_lon;
    }

    public function setCityLon(?string $city_lon): self
    {
        $this->city_lon = $city_lon;

        return $this;
    }
}
