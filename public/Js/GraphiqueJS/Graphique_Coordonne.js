let controleur = null;

export function Connecter(_controleur) {
    controleur = _controleur;
}

let PixelParPrix_Y = 0;

export function DefinirCoordonneXYListeVente() {
    //Selon des coordonne x y NON DILATER
    const donnee = controleur.donnee;

    const img_largeur = donnee.Largeur;
    const img_hauteur = donnee.Hauteur;

    const ModeDate = donnee.ModeDate; //reel ou distinct

    let premiereTransaction = controleur.donnee.PremiereTransaction;
    let derniereTransaction = controleur.donnee.DerniereTransaction;

    let plusPetitTick = premiereTransaction.date.getTime();
    let plusGrandTick = derniereTransaction.date.getTime();

    let prixMax = donnee.GPPositifRecord;
    let prixMin = donnee.GPNegatifRecord;

    let PixelParTick_X = img_largeur / (plusGrandTick - plusPetitTick) * controleur.donnee.ScaleX; //CORRECTE
    PixelParPrix_Y = img_hauteur / (prixMax - prixMin) * controleur.donnee.ScaleY; //CORRECTE

    let ListeVente = donnee.ListeVente;

    for (let i = 0; i < ListeVente.length; i++) {
        let vente = ListeVente[i];

        //X
        let x = 0;

        if (ModeDate == "reel") {
            let venteTick = vente.date.getTime();

            x = (venteTick - plusPetitTick) * PixelParTick_X; //CORRECTe
        } else if (ModeDate == "distinct") {
            x = vente.ordre / derniereTransaction.ordre * img_largeur * controleur.donnee.ScaleX;
        }

        ListeVente[i]['graphe_x'] = x + controleur.donnee.DeplacementXPixel;

        //Y
        let prix = 0;
        if (controleur.donnee.ValeurAffichage == "gp") {
            prix = ListeVente[i]['gp'];
        } else {
             prix = ListeVente[i]['gpTotale'];
        }
        ListeVente[i]['graphe_y'] = ObtenirYSelonPrix(prix);
    }
}

export function ObtenirPrixSelonY(y) {
    return (y - controleur.donnee.DeplacementYPixel) / PixelParPrix_Y + controleur.donnee.GPNegatifRecord; //A verifier

}

export function ObtenirYSelonPrix(prix) {
    return (prix - controleur.donnee.GPNegatifRecord) * PixelParPrix_Y + controleur.donnee.DeplacementYPixel; //A verifier
}