window.addEventListener('DOMContentLoaded', async () => {
    const donnee = await import("./Graphique_donnee.js");
    const Coordonnee = await import("./Graphique_Coordonne.js");
    const generateur = await import("./Graphique_generateur.js");
    const souris = await import("./Graphique_souris.js");

    const controleur = {
        canvas: document.getElementById('grapheJS'),
        contexte: document.getElementById('grapheJS').getContext("2d"),
        donnee: donnee,
        Coordonnee: Coordonnee,
        generateur: generateur,
        actualiser: function () {
            Coordonnee.DefinirCoordonneXYListeVente();
            generateur.Actualiser();
        }
    };

    donnee.Connecter(controleur);
    Coordonnee.Connecter(controleur);
    generateur.Connecter(controleur);
    souris.Connecter(controleur);


    // Fonction pour adapter le canvas à la taille réelle de l’affichage
    function RedimensionnerZoneDeDessin() {
        const rect = controleur.canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;

        let largeur = rect.width * ratio;
        let hauteur = rect.height * ratio;

        controleur.canvas.width = largeur;
        controleur.canvas.height = hauteur;

        donnee.DefinirTaille(largeur, hauteur);

        controleur.contexte.setTransform(1, 0, 0, 1, 0, 0);
        controleur.contexte.scale(ratio, ratio);
    
        controleur.actualiser();
    }

    document.addEventListener('mousemove', function (event) {
        controleur.actualiser();
    });

    //Redimension
    window.addEventListener('resize', function (event) {
        RedimensionnerZoneDeDessin();
    });

    RedimensionnerZoneDeDessin();

    donnee.ReinitParametreAffichage();

    controleur.actualiser();
});