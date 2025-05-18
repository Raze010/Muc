let controleur = null;

export let posSourisX = -100;
export let posSourisY = -100;

export let ListeVente = null;

export let PremiereTransaction = null;
export let DerniereTransaction = null;

export let ModeAffichage = "reel";

export let Largeur = 0;
export let Hauteur = 0;

export let GPPositifRecord = 0;
export let GPNegatifRecord = 0;

export let VentePositifRecord = null;
export let VenteNegatifRecord = null;

export function DefinirTaille(largeur, hauteur) {
    Largeur = largeur;
    Hauteur = hauteur;
}

export function Connecter(_controleur) {
    controleur = _controleur;

    ListeVente = window.listeVente;

    for (let i = 0; i < ListeVente.length; i++) {
        let vente = ListeVente[i];
        
        vente.date = new Date(vente.date);

        let gpActu = vente.gpTotale;

        if (GPPositifRecord < gpActu) {
            GPPositifRecord = gpActu;
            VentePositifRecord = vente;
        }
        if (GPNegatifRecord > gpActu) {
            GPNegatifRecord = gpActu;
            VenteNegatifRecord = vente;
        }
    }

    PremiereTransaction = ListeVente[0];
    DerniereTransaction = ListeVente[ListeVente.length - 1];

    ModeAffichage = window.modeAffichage;

    document.addEventListener('mousemove', function (event) {
        const rect = controleur.canvas.getBoundingClientRect();

        posSourisX = event.clientX - rect.left; // Position horizontale dans la fenêtre
        posSourisY = event.clientY - rect.top; // Position verticale dans la fenêtre
    });
}