<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private $categorie;

    #[ORM\Column(type: 'text', nullable: true)]
    private $link;

    #[ORM\Column(type: 'text', nullable: true)]
    private $imageLink;

    #[ORM\Column(type: 'text', nullable: true)]
    private $additionalImageLink;

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'product_condition')]
    private $productCondition; // Renamed from 'condition'

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $availability;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private $price;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private $salePrice;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $brand;

    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    private $ean;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mpn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->imageLink;
    }

    public function setImageLink(?string $imageLink): self
    {
        $this->imageLink = $imageLink;
        return $this;
    }

    public function getAdditionalImageLink(): ?string
    {
        return $this->additionalImageLink;
    }

    public function setAdditionalImageLink(?string $additionalImageLink): self
    {
        $this->additionalImageLink = $additionalImageLink;
        return $this;
    }

    public function getProductCondition(): ?string
    {
        return $this->productCondition;
    }

    public function setProductCondition(?string $productCondition): self
    {
        $this->productCondition = $productCondition;
        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(?string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getSalePrice(): ?string
    {
        return $this->salePrice;
    }

    public function setSalePrice(?string $salePrice): self
    {
        $this->salePrice = $salePrice;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan(?string $ean): self
    {
        $this->ean = $ean;
        return $this;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): self
    {
        $this->mpn = $mpn;
        return $this;
    }
}
