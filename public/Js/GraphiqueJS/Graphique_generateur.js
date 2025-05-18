let controleur = null;

export function Connecter(_controleur) {
    controleur = _controleur;
}

const couleurBlanche = "#ffffff";
const couleurVerte = "#008020";
const couleurRouge = "#f00020";
const couleurGris = "#70726E";

let listeVente = null;
let contexte = null;
let donnee = null;
let canvas = null;
let coordonnee = null;

export function Actualiser() {
    //Misc
    contexte = controleur.contexte;
    listeVente = controleur.donnee.ListeVente;
    donnee = controleur.donnee;
    canvas = controleur.canvas;
    coordonnee = controleur.Coordonnee;

    contexte.clearRect(0, 0, canvas.width, canvas.height);
    //fin misc

    //Souris
    contexte.fillStyle = couleurBlanche;
    contexte.fillRect(donnee.posSourisX - 5, donnee.posSourisY - 5, 10, 10);
    //fin souris

    //Echelon
    DessinerEchelon(500);
    //fin echelon

    //Record
    DessinerRecord(donnee.VentePositifRecord, true);
    DessinerRecord(donnee.VenteNegatifRecord, false);
    //fin record

    //Courbe
    DessinerCourbe();
    //fin courbe

    //Transaction
    DessinerTransaction();
    //fin transaction

}

function DessinerEchelon (ecart) {
    let PrixDebut = (donnee.GPPositifRecord / ecart).toFixed(0) * ecart;
    let PrixFin = (donnee.GPNegatifRecord / ecart).toFixed(0) * ecart;

    let PrixActuelle = PrixDebut;

    while (PrixActuelle >= PrixFin) {
        let y = coordonnee.ObtenirPosYSelonPrix(PrixActuelle);

        DessinLignePointille(0,y,donnee.Largeur,y,couleurGris,5,5);

        DessinTexte(PrixActuelle + " $",0,y,couleurGris,10,0,1);

        PrixActuelle -= ecart;
    }
}

function DessinerRecord (vente, Positif) {
    let couleur = couleurGris;

    if(vente.gpTotale > 0) {
        couleur = couleurVerte;
    } else if (vente.gpTotale < 0) {
        couleur = couleurRouge;
    }

    let x = coordonnee.ObtenirPosXSelonTransaction(vente);
    let y = coordonnee.ObtenirPosYSelonPrix(vente.gpTotale);

    DessinLignePointille(0, y, donnee.Largeur, y, couleur,5,5);

    let texte = "";

    if (Positif) {
        texte = "Record positif: +" +vente.gpTotale.toFixed(2) +" $";
    } else {
        texte = "Record negatif: " +vente.gpTotale.toFixed(2) +" $";
    }

    let orientationVertical = 0;
    if (Positif) {
        orientationVertical = 1;
    } else {
        orientationVertical = -1;
    }

    DessinTexte(texte, x, y, couleur, 10, 0, orientationVertical);
}

function DessinerCourbe () {
    let xAvant = 0;
    let yAvant = 0;

    for (let i = 0; i < listeVente.length; i++) {
        let vente = listeVente[i];

        let gpTotale = vente.gpTotale;
        let gp = vente.gp;

        let x = coordonnee.ObtenirPosXSelonTransaction(vente);
        let y = coordonnee.ObtenirPosYSelonPrix(gpTotale);

        if (i != 0) {
            let couleur = couleurGris;

            if (gpTotale < 0) {
                couleur = couleurRouge;
            } else {
                couleur = couleurVerte;
            }
            DessinLigne(xAvant, yAvant, x, y, couleur);
        }

        xAvant = x;
        yAvant = y;
    }
}

function DessinerTransaction (){
    for (let i = 0; i < listeVente.length; i++) {
        let vente = listeVente[i];

        let gpTotale = vente.gpTotale;
        let gp = vente.gp;

        let x = coordonnee.ObtenirPosXSelonTransaction(vente);
        let y = coordonnee.ObtenirPosYSelonPrix(gpTotale);

        let couleurTransaction = couleurGris;

        if (gp < 0) {
            couleurTransaction = couleurRouge;
        } else if (gp > 0) {
            couleurTransaction = couleurVerte;
        }

        let taille = 2;

        if (Math.abs(gp) >= 50) {
            taille = 4;
        } else if (Math.abs(gp) >= 20) {
            taille = 3;
        }

        DessinCercle(x, y, taille, couleurTransaction);
    }
}

//#region dessin

//orientation horizontale : -1 = gauche, 0 = centre, 1 = droite
//orientation verticale : -1 = bas, 0 = centre, 1 = haut
function DessinTexte (texte, x, y, couleur, taillePolice, orientationHorizontal, oritentationVertical) {

    const tailleTexte = contexte.measureText(texte);
    const largeurTexte = tailleTexte.width;
    const hauteurTexte = tailleTexte.actualBoundingBoxAscent + tailleTexte.actualBoundingBoxDescent;

    if (orientationHorizontal == -1) {
        x -= largeurTexte;
    } else if (orientationHorizontal == 1) {
        x += largeurTexte;
    } else if (orientationHorizontal == 0) {
        x -= largeurTexte / 2;
    }

    if (oritentationVertical == -1) {
        y += hauteurTexte;
    } else if (oritentationVertical == 1) {
        y -= hauteurTexte;
    }

    if(x < 0) {
        x = 0;
    } else if (x > donnee.Largeur){
        x = donnee.Largeur;
    }

    if(y < 0){
        y = 0;
    } else if (y > donnee.Hauteur){
        y = donnee.Hauteur;
    }

    let font = taillePolice + "px Oswald";

    contexte.fillStyle = couleur;
    contexte.font = font;
    contexte.fillText(texte, x, y); 
}

function DessinCercle(x, y, r, couleur) {
    contexte.beginPath();
    contexte.fillStyle = couleur;
    contexte.arc(x, y, r, 0, Math.PI * 2);
    contexte.fill();
}

function DessinLigne(x1, y1, x2, y2, couleur) {
    contexte.beginPath();
    contexte.strokeStyle = couleur;
    contexte.moveTo(x1, y1);
    contexte.lineTo(x2, y2);
    contexte.stroke();
}

function DessinLignePointille(x1, y1, x2, y2, couleur,espaceEntreTrait,TailleTrait) {
    contexte.beginPath();
    contexte.setLineDash([TailleTrait,espaceEntreTrait]);
    contexte.strokeStyle = couleur;
    contexte.moveTo(x1, y1);
    contexte.lineTo(x2, y2);
    contexte.stroke();
    contexte.setLineDash([]);
}

//#endregion