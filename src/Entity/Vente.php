<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cours::class)]
    #[ORM\JoinColumn(name: "idCours", referencedColumnName: "id")]
    private ?Cours $cours = null;
    
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "idUtilisateur", referencedColumnName: "id")]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?float $SommeInvestis = null;

    #[ORM\Column]
    private ?float $PrixCoursAchat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateAchat = null;

    #[ORM\Column]
    private ?float $PrixCoursVente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateVente = null;

    #[ORM\Column]
    private ?int $EffetLevier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSommeInvestis(): ?float
    {
        return $this->SommeInvestis;
    }

    public function setSommeInvestis(float $SommeInvestis): static
    {
        $this->SommeInvestis = $SommeInvestis;

        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->DateAchat;
    }

    public function setDateAchat(\DateTimeInterface $DateAchat): static
    {
        $this->DateAchat = $DateAchat;

        return $this;
    }

    public function getDateVente(): ?\DateTimeInterface
    {
        return $this->DateVente;
    }

    public function setDateVente(\DateTimeInterface $DateVente): static
    {
        $this->DateVente = $DateVente;

        return $this;
    }
}
