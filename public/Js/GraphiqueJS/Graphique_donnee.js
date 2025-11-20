let controleur = null;

export let posSourisX = -100;
export let posSourisY = -100;

export let ListeVente = null;

export let PremiereTransaction = null;
export let DerniereTransaction = null;

export let ModeDate = "reel";
export let ModeAffichage = "transaction";
export let ValeurAffichage = "gpTotale";

export let Largeur = 0;
export let Hauteur = 0;

export function DefinirTaille(largeur, hauteur) {
    Largeur = largeur;
    Hauteur = hauteur;
}

export let GPPositifRecord = -1000000;
export let GPNegatifRecord = 1000000;

export let VentePositifRecord = null;
export let VenteNegatifRecord = null;

//#region deplacement graphe

export let DeplacementXPixel = 0;
export let DeplacementYPixel = 0;

export function DefinirDeplacement(x, y) {
    DefinirDeplacementPixelX(x);
    DefinirDeplacementPixelY(y);
}

export function DefinirDeplacementPixelX(x) {
    DeplacementXPixel = x;
}

export function DefinirDeplacementPixelY(y) {
    DeplacementYPixel = y;
}

//#endregion

//#region zoom graphe

export let ScaleX = 1;
export let ScaleY = 1;

export function DefinirScale(x, y) {
    DefinirScaleX(x);
    DefinirScaleY(y);
}

export function DefinirScaleX(x) {
    ScaleX = x;
}

export function DefinirScaleY(y) {
    ScaleY = y;
}

//#endregion

export let ListeTransactionSelectionner;

export function ReinitParametreAffichage() {
    DeplacementXPixel = 0;
    DeplacementYPixel = 0;
    ScaleX = 1;
    ScaleY = 1;
}

export function Connecter(_controleur) {
    controleur = _controleur;

    ModeDate = window.modeDate;
    ModeAffichage = window.modeAffichage;
    ValeurAffichage = window.ValeurAffichage;

    ListeVente = window.listeVente;

    for (let i = 0; i < ListeVente.length; i++) {
        let vente = ListeVente[i];

        vente.date = new Date(vente.date);
        let gpActu = vente.gpTotale;

        if (ValeurAffichage == 'gp') {
            gpActu = vente.gp;
        }

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

    document.addEventListener('mousemove', function (event) {
        const rect = controleur.canvas.getBoundingClientRect();

        posSourisX = event.clientX - rect.left; // Position horizontale dans la fenêtre
        posSourisY = Hauteur - (event.clientY - rect.top); // Position verticale dans la fenêtre
    });
    ListeTransactionSelectionner = [];
    function update() {
        if (window.ListeTransactionSelectionner == null || ListeTransactionSelectionner == null) {
            ListeTransactionSelectionner = [];
        }
        ListeTransactionSelectionner = window.ListeTransactionSelectionner;
        requestAnimationFrame(update);
    }

    requestAnimationFrame(update);
}