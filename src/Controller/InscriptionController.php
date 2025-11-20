<?php

namespace App\Controller;

use App\Back\Graphique\GenGraphique;
use App\Back\Calcul;
use App\Back\LectureTransactionEtoro;
use App\Back\TransactionHelper;
use App\Entity\Vente;
use App\Repository\VenteRepository;
use DateTime;
use PhpParser\Node\Expr\Array_;
use Proxies\__CG__\App\Entity\Cours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\MISC\PHPHelper;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function index(): Response
    {
        return $this->render('Inscription.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }
    
    #[Route('/verifierAdresseMail', name: 'verifierAdresseMail', methods: ['POST'])]
    public function verifierAdresseMail(Request $request, SessionInterface $session, ManagerRegistry $doctrine): JsonResponse
    {        
        $nom = $request->request->get('nom');
        $adresseMail = $request->request->get('adresseMail');
        $mdp = $request->request->get('mdp');

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($nom);
        $utilisateur->setAdresseMail($adresseMail);
        $utilisateur->setMdp($mdp);

        // Utiliser le repository de l'entité Utilisateur
        $repository = $doctrine->getRepository(Utilisateur::class);
        // Appeler la méthode TrouverUtilisateur (ou une méthode similaire du repository)
        $existeDeja = $repository->TrouverAdresseMail($adresseMail) != null;

        if($existeDeja){
            return new JsonResponse(['success' => false, 'message'=>'Cette adresse mail est déjâ utilisé']);
        }

        $repository->addUtilisateur($utilisateur);
        $session->set('utilisateur', value: $utilisateur);

        return new JsonResponse(['success' => true, 'nom' => $adresseMail]);
    }
}