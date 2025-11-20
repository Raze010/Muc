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

class BienvenueController extends AbstractController
{
    #[Route('/bvn', name: 'bienvenue', methods: ['GET'])]
    public function index(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Vente::class);

        $utilisateur = $session->get('utilisateur', );
        $listeVente = $repository->TrouverVente($utilisateur);

        $filtreCours = $request->query->get('cours', '');
        $modeGP = $request->query->get('modeGP', 'tout');
        $modeAffichage = $request->query->get('modeAffichage', 'transaction');
        $modeDate = $session->get('modeDate'); //REEL, IDENTIQUE

        $transactionHelper = new TransactionHelper();
        $transactionHelper->modeAffichage = $modeAffichage;
        $transactionHelper->modeGP = $modeGP;
        $transactionHelper->cours = $filtreCours;

        $description = "Toutes les transactions";

        if($modeGP == "gain") {
            $description = "Tous les gains";
        } else if ($modeGP == "perte") {
            $description = "Toutes les pertes";
        }

        $listeVente = $transactionHelper->FiltrerListeVente($listeVente);
        $listeVenteJS = $transactionHelper->ObtenirListeVente_JS($listeVente);

        $sommeTotale = Calcul::getGpTotale($listeVente);

        $sousMessage = $session->get('bvn_message');
        $sousMessageClasse = $session->get('bvn_message_couleur');

        $listeCours = $transactionHelper->ObtenirListeCours($listeVente);

        return $this->render('Bienvenue.html.twig', [
            'nom' => $utilisateur->getNom(),
            'sommeTotale' => $sommeTotale,
            'sousMessage' => $sousMessage,
            'sousMessageClasse' => $sousMessageClasse,
            'listeVente' => $listeVente,
            'listeVenteJS' => $listeVenteJS,
            'listeCours'=>$listeCours,
            'fraisSup' => 0,
            'sommeTotaleAvecFrais' => $sommeTotale,
            'modeDate' => $modeDate,
            'Description'=> $description,
            'FiltreCours' => $filtreCours
        ]);
    }

    #[Route('/importerTransactionEtoro', name: 'importerTransactionEtoro', methods: ['POST'])]
    public function ImporterTransactionEtoro(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $utilisateur = $session->get('utilisateur');

        $fichier = $request->files->get('fichier_etoro');

        if ($fichier) {
            $cheminTemporaire = $fichier->getPathname();

            // Passe le chemin temporaire au lecteur de fichiers
            LectureTransactionEtoro::Lire($cheminTemporaire, $utilisateur, $session, $doctrine);
        }

        return $this->redirectToRoute('bienvenue');
    }

    #[Route('/ReninitMessageBvn', name: 'ReinitMessageBvn', methods: ['GET'])]
    public function ReinitMessageBvn(Request $request, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $session->set('bvn_message', '');

        return $this->redirectToRoute('bienvenue');
    }

    #[Route('/image_cours/{id}', name: 'imageCours')]
    public function displayImage(int $id, SessionInterface $session, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Cours::class);

        $cours = $repository->getFromId($id);

        if ($cours == null) {
            return new Response();
        }

        $blob = $cours->getImage();

        if ($blob == null) {
            return new Response();
        }

        $imageData = stream_get_contents($cours->getImage());

        if ($imageData === false) {
            throw new \RuntimeException('Erreur de lecture du BLOB');
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', 'image/jpeg'); // ou 'image/png'

        return $response;
    }
}
