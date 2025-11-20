<?php

namespace App\Controller;

use App\Back;
use App\Back\LectureTransactionEtoro;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\Persistence\ManagerRegistry;
use App\MISC\PHPHelper;
use App\Entity\Utilisateur;
use App\Repository;

class ConnectionController extends AbstractController {

    #[Route('/', name: 'connection_index', methods: ['GET'])]
    public function index (Request $request,ManagerRegistry $registry): Response
    {   
        return $this->render('ConnectionVue.html.twig');
    }

    #[Route('/VerifierDonneeConnexion', name: 'VerifierDonneeConnexion', methods: ['POST'])]
    public function VerifierDonneeConnexion(Request $request, SessionInterface $session, ManagerRegistry $doctrine): JsonResponse
    {        
        $nom = $request->request->get('nom');
        $mdp = $request->request->get('mdp');

        $user = $this->trouverUtilisateurParNom($nom, $doctrine);

        if($user == null) {
            return new JsonResponse(data: ['success' => false, 'message'=>'Le nom d\'utilisateur ou le mot de passe est incorrecte']);
        }

        if(!password_verify($mdp,$user->getMdp())) {
            return new JsonResponse(data: ['success' => false, 'message'=>'Le nom d\'utilisateur ou le mot de passe est incorrecte']);
        }

        $session->set('utilisateur', value: $user);

        return new JsonResponse(['success' => true, 'nom' => $nom]);
    }

    public function trouverUtilisateurParNom($nom, ManagerRegistry $doctrine):?Utilisateur
    {
        // Utiliser le repository de l'entité Utilisateur
        $repository = $doctrine->getRepository(Utilisateur::class);
        // Appeler la méthode TrouverUtilisateur (ou une méthode similaire du repository)
        $utilisateur = $repository->TrouverUtilisateur($nom);

        // Vérifier si un utilisateur a été trouvé
        return $utilisateur;
    }
}
