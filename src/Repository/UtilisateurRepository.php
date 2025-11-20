<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function TrouverUtilisateur($nom)
    {
      return $this->findOneBy(['Nom' => $nom]);
    }

    public function TrouverAdresseMail ($adresseMail){
      return $this->findOneBy(["adresseMail"=>$adresseMail]);
    }

    public function addUtilisateur($utilisateur): Utilisateur
    {
        $this->getEntityManager()->persist($utilisateur);
        $this->getEntityManager()->flush();
    
        return $utilisateur;
    }
}
