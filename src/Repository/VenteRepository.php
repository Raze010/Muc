<?php

namespace App\Repository;

use App\Entity\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vente>
 */
class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vente::class);
    }

    public function TrouverVente($utilisateur)
    {
      return $this->findBy(['utilisateur' => $utilisateur]);
    }
    
    public function addVente($Vente): Vente
    {
        $this->getEntityManager()->persist($Vente);
        $this->getEntityManager()->flush();
    
        return $Vente;
    }
}
