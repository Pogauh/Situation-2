<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;

//------------------------------------------

#[ApiResource( 
    operations:[new Get(normalizationContext:['groups'=>'produit:item']),
            new GetCollection(normalizationContext:['groups'=>'produit:list']),
            new Delete(),
        ]
)]


#[ORM\Entity(repositoryClass: ProduitRepository::class)]

class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[groups(['produit:list','produit:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[groups(['produit:list','produit:item'])]
    private ?string $nom = null;

    #[ORM\Column]
    #[groups(['produit:list','produit:item'])]
    private ?int $prix = null;

    #[ORM\Column(type: Types::TEXT)]
    #[groups(['produit:list','produit:item'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[groups(['produit:list','produit:item'])]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
