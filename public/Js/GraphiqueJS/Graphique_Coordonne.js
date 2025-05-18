let controleur = null;

export function Connecter(_controleur) {
    controleur = _controleur;
}

export function ObtenirPosXSelonTransaction (vente) {
    let padding = 20;

    let Largeur = controleur.donnee.Largeur - padding;

    let premiereTransaction = controleur.donnee.PremiereTransaction;
    let derniereTransaction = controleur.donnee.DerniereTransaction;

    let modeAffichage = controleur.donnee.ModeAffichage;

    if (modeAffichage == "reel") {
        console.log("du");
        let plusPetitTick = premiereTransaction.date.getTime();
        let plusGrandTick = derniereTransaction.date.getTime();

        let venteTick = vente.date.getTime();
        console.log("plus petit = " +plusPetitTick +" plus grand = " +plusGrandTick);

        let x = (venteTick - plusPetitTick) / (plusGrandTick - plusPetitTick) * Largeur;
    
        return x + padding / 2;
    } else if (modeAffichage == "distinct") {
        return vente.ordre / derniereTransaction.ordre * Largeur + padding / 2;
    }
}

export function ObtenirPosYSelonPrix (prix) {
    const padding = 50;

    let hauteur = controleur.donnee.Hauteur - padding;

    prix -= controleur.donnee.GPNegatifRecord;

    let pixelParPrix = (controleur.donnee.GPPositifRecord - controleur.donnee.GPNegatifRecord) / hauteur;

    return hauteur - prix / pixelParPrix + padding / 2;
}