<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="warehouse")
 * @ORM\Entity(repositoryClass="App\Repository\WarehouseRepository")
 */
class Warehouse
{
    /**
     * @ORM\Column(type="string", length=36)
     * @ORM\Id
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="warehouses")
     * @ORM\JoinColumn(nullable=true)
     * @var City
     */
    private $city;

    /**
     * Warehouse constructor.
     * @param string $id
     * @param string $name
     * @param City $city
     */
    public function __construct(string $id, string $name, City $city)
    {
        $this->id = $id;
        $this->name = $name;
        $this->city = $city;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }
}
