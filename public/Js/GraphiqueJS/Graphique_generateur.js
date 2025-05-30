let controleur = null;

export function Connecter(_controleur) {
    controleur = _controleur;
}

const couleurBlanche = "#ffffff";
const couleurVerte = "#008020";
const couleurRouge = "#f00020";
const couleurGris = "#70726E";
const couleurNoire = "#000000";

let listeVente = null;
let contexte = null;
let donnee = null;
let canvas = null;
let coordonnee = null;

export let ReticuleDonnee = null;

export function Actualiser() {
    //Misc
    contexte = controleur.contexte;
    listeVente = controleur.donnee.ListeVente;
    donnee = controleur.donnee;
    canvas = controleur.canvas;
    coordonnee = controleur.Coordonnee;

    contexte.clearRect(0, 0, canvas.width, canvas.height);
    //fin misc

    //Echelon
    DessinerEchelon();
    //fin echelon

    //Record
    if (listeVente.length > 2 && donnee.VentePositifRecord.gpTotale != 0 && donnee.VenteNegatifRecord.gpTotale != 0) {
        DessinerRecord(donnee.VentePositifRecord, true);
        DessinerRecord(donnee.VenteNegatifRecord, false);
    }
    //fin record

    //Courbe
    DessinerCourbe();
    //fin courbe

    //Transaction
    DessinerToutesLesTransaction();
    //fin transaction

    //Souris/Reticule
    const sourisEspaceEntreTrait = 5;
    const sourisTailleTrait = 5;

    ReticuleDonnee = PosReticuleVenteSelectionner(listeVente);

    //DessinPrix
    if (ReticuleDonnee.vente != null) {

        DessinLignePointille(ReticuleDonnee.x, 0, ReticuleDonnee.x, donnee.Hauteur, couleurGris, sourisEspaceEntreTrait, sourisTailleTrait);
        DessinLignePointille(0, ReticuleDonnee.y, donnee.Largeur, ReticuleDonnee.y, couleurGris, sourisEspaceEntreTrait, sourisTailleTrait);

        //dessin gauche -> gpTotale

        let gpTotale = ReticuleDonnee.vente.gpTotale;

        let texte = gpTotale.toFixed(2) + " $";

        let couleur = couleurGris;
        if (gpTotale > 0) {
            couleur = couleurVerte;
        } else if (gpTotale < 0) {
            couleur = couleurRouge;
        }

        DessinTexte(texte, 0, ReticuleDonnee.y, couleur, 10, 0, 0, true);
        //Fin gpTotale

        //dessin (xLevier)+gp 
        let levier = ReticuleDonnee.vente.levier;

        let gp = ReticuleDonnee.vente.gp;

        texte = "";

        if (levier != 1) {
            texte += "(x" + levier + ") ";
        }

        if (gp > 0) {
            texte += "+" + gp.toFixed(2) + " $";
        } else {
            texte += gp.toFixed(2) + " $";
        }

        couleur = couleurGris;
        if (gp > 0) {
            couleur = couleurVerte;
        } else if (gp < 0) {
            couleur = couleurRouge;
        }

        DessinTexte(texte, donnee.Largeur, ReticuleDonnee.y, couleur, 10, -1, 0, true);
        //Fin gp

        //dessin haut cours_noù
        texte = ReticuleDonnee.vente.cours_nom;

        couleur = couleurGris;

        DessinTexte(texte, ReticuleDonnee.x, 0, couleur, 10, 0, -1, true);
        //fin dessin haut

        //dessin date

        let date = ReticuleDonnee.vente.date;

        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0'); // Les mois vont de 0 à 11
        let day = String(date.getDate()).padStart(2, '0');

        let formattedDate = `${day}/${month}/${year} `;

        DessinTexte(formattedDate, ReticuleDonnee.x, donnee.Hauteur, couleurGris, 10, 0, 1, true);

        //fin dessin date
    }

    //Fin dessin prix

    //fin souris
}

function PosReticuleVenteSelectionner(listeVente) {
    let PosSourisX = donnee.posSourisX;
    let PosSourisY = donnee.posSourisY;

    let venteTrouver_x = 0;
    let venteTrouver_y = 0;
    let venteTrouver = null;
    let tolerance = 20;
    let plusPetiteDistance = 1000000;

    let i = 0;
    while (i < listeVente.length) {
        let vente = listeVente[i];

        let x = vente['graphe_x'];
        let y = vente['graphe_y'];

        let distanceX = x - PosSourisX;
        let distanceY = y - PosSourisY;

        let distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);

        if (distance < tolerance && distance < plusPetiteDistance) {
            plusPetiteDistance = distance;
            venteTrouver = vente;
            venteTrouver_x = x;
            venteTrouver_y = y;
        }

        i++;
    }

    if (venteTrouver != null) {
        DessinerTransaction(venteTrouver, true);
        return { "x": venteTrouver_x, "y": venteTrouver_y, "vente": venteTrouver };
    }

    return { "x": PosSourisX, "y": PosSourisY, "vente": null };
}

function DessinerEchelon() {
    let yMax = donnee.Hauteur;
    let yMin = 0;

    let prixMax = coordonnee.ObtenirPrixSelonY(yMax);
    let prixMin = coordonnee.ObtenirPrixSelonY(yMin);

    let tailleEnPrix = Math.abs(prixMax - prixMin);

    let ecart = 100;

    if (tailleEnPrix > 10000) {
        ecart = 2000;
    } else if (tailleEnPrix > 5000) {
        ecart = 1000;
    } else if (tailleEnPrix > 2500) {
        ecart = 500;
    } else if (tailleEnPrix > 1250) {
        ecart = 250;
    }

    let PrixDebut = Math.round(prixMax / ecart) * ecart;
    let PrixFin = Math.round(prixMin / ecart) * ecart;

    let PrixActuelle = PrixDebut;

    while (PrixActuelle >= PrixFin) {
        let y = coordonnee.ObtenirYSelonPrix(PrixActuelle);

        if (y < 0 || y > donnee.Hauteur) {
            PrixActuelle -= ecart;
            continue;
        }

        DessinLignePointille(0, y, donnee.Largeur, y, couleurGris, 5, 5);

        DessinTexte(PrixActuelle + " $", 0, y, couleurGris, 10, 1, 1, false);

        PrixActuelle -= ecart;
    }
}

function DessinerRecord(vente, Positif) {
    let couleur = couleurGris;

    if (vente.gpTotale > 0) {
        couleur = couleurVerte;
    } else if (vente.gpTotale < 0) {
        couleur = couleurRouge;
    }

    let x = vente['graphe_x'];
    let y = vente['graphe_y'];

    // DessinLignePointille(0, y, donnee.Largeur, y, couleur, 5, 5);

    let texte = "";

    if (Positif) {
        texte = "Record positif: +" + vente.gpTotale.toFixed(2) + " $";
    } else {
        texte = "Record negatif: " + vente.gpTotale.toFixed(2) + " $";
    }

    let orientationVertical = 0;
    if (Positif) {
        orientationVertical = 1;
    } else {
        orientationVertical = -1;
    }

    DessinTexte(texte, x, y, couleur, 10, 0, orientationVertical, true);
}

function DessinerCourbe() {
    let xAvant = 0;
    let yAvant = 0;

    for (let i = 0; i < listeVente.length; i++) {
        let vente = listeVente[i];

        let gpTotale = vente.gpTotale;

        const x = vente['graphe_x'];
        const y = vente['graphe_y'];

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

let a = true;

function DessinerToutesLesTransaction() {
    for (let i = 0; i < listeVente.length; i++) {
        let vente = listeVente[i];

        DessinerTransaction(vente, false);
    }
}

function DessinerTransaction(vente, estSelectionner) {
    let gp = vente.gp;

    const x = vente['graphe_x'];
    const y = vente['graphe_y'];
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

    if(estSelectionner) {
        taille = 5;
    }

    taille *= controleur.donnee.ScaleX * 0.8;
    if(taille < 2){
        taille = 2;
    }

    DessinCercle(x, y, taille, couleurTransaction);
}

//#region dessin

//orientation horizontale : -1 = gauche, 0 = centre, 1 = droite
//orientation verticale : -1 = bas, 0 = centre, 1 = haut
function DessinTexte(texte, x, y, couleur, taillePolice, orientationHorizontal, oritentationVertical, fondNoir) {

    const tailleTexte = contexte.measureText(texte);
    let largeurTexte = tailleTexte.width;
    let hauteurTexte = tailleTexte.actualBoundingBoxAscent + tailleTexte.actualBoundingBoxDescent;

    if (orientationHorizontal == -1) {
        x -= largeurTexte;
    } else if (orientationHorizontal == 1) {
        x += largeurTexte / 2;
    } else if (orientationHorizontal == 0) {
        x -= largeurTexte / 2;
    }

    largeurTexte += 10;
    hauteurTexte += 5;

    if (oritentationVertical == 1) {
        y += hauteurTexte;
    } else if (oritentationVertical == -1) {
        y -= hauteurTexte + 5;
    }

    if(x > controleur.donnee.Largeur){
        x = controleur.donnee.Largeur;
    } 
    if(x < 0) {
        x = 0;
    }

    if(y > controleur.donnee.Hauteur){
        y = controleur.donnee.Hauteur;
    }
    if(y < 0) {
        y = 0;
    }

    let font = taillePolice + "px Oswald";

    if (fondNoir) {
        DessinRectangle(x - 5, y + hauteurTexte - 5, largeurTexte, hauteurTexte, couleurNoire);
    }

    contexte.fillStyle = couleur;
    contexte.font = font;
    contexte.fillText(texte, x, ObtenirYConvertis(y));
}

function DessinRectangle(x, y, largeur, hauteur, couleur) {
    contexte.beginPath();
    contexte.fillStyle = couleur;
    contexte.fillRect(x, ObtenirYConvertis(y), largeur, hauteur);
    contexte.closePath();
}

function DessinCercle(x, y, r, couleur) {
    contexte.beginPath();
    contexte.fillStyle = couleur;
    contexte.arc(x, ObtenirYConvertis(y), r, 0, Math.PI * 2);
    contexte.fill();
}

function DessinLigne(x1, y1, x2, y2, couleur) {
    contexte.beginPath();
    contexte.lineWidth = 2;
    contexte.strokeStyle = couleur;
    contexte.moveTo(x1, ObtenirYConvertis(y1));
    contexte.lineTo(x2, ObtenirYConvertis(y2));
    contexte.stroke();
}

function DessinLignePointille(x1, y1, x2, y2, couleur, espaceEntreTrait, TailleTrait) {
    contexte.beginPath();
    contexte.setLineDash([TailleTrait, espaceEntreTrait]);
    contexte.lineWidth = 1;
    contexte.strokeStyle = couleur;
    contexte.moveTo(x1, ObtenirYConvertis(y1));
    contexte.lineTo(x2, ObtenirYConvertis(y2));
    contexte.stroke();
    contexte.setLineDash([]);
}

function ObtenirYConvertis(y) {
    return controleur.donnee.Hauteur - y;
}

//#endregion