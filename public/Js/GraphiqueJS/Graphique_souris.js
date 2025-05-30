let controleur = null;

let DeplacementSourisEnCours = false;
let DeplacementSouris_DepartX = 0;
let DeplacementSouris_DepartY = 0;

let DeplacementGraphe_DepartX = 0;
let DeplacementGraphe_DepartY = 0;

let sourisSurGraphe = false;

function ActiverDeplacementSouris() {
    DeplacementSourisEnCours = true;
    DeplacementSouris_DepartX = controleur.donnee.posSourisX;
    DeplacementSouris_DepartY = controleur.donnee.posSourisY;
    DeplacementGraphe_DepartX = controleur.donnee.DeplacementXPixel;
    DeplacementGraphe_DepartY = controleur.donnee.DeplacementYPixel;
}

function EffectuerDeplacementSouris() {
    if (DeplacementSourisEnCours == false) {
        return;
    }

    let deplacementX = DeplacementGraphe_DepartX + (controleur.donnee.posSourisX - DeplacementSouris_DepartX);
    let deplacementY = DeplacementGraphe_DepartY + (controleur.donnee.posSourisY - DeplacementSouris_DepartY);

    controleur.donnee.DefinirDeplacement(deplacementX, deplacementY);

    controleur.actualiser();
}

function DesactiverDeplacementSouris() {
    DeplacementSourisEnCours = false;
}

export function Connecter(_controleur) {
    controleur = _controleur;

    const image = controleur.canvas; // adapte le sélecteur à ton cas

    image.addEventListener('mousedown', function (event) {
        event.preventDefault();
        if (event.button === 0) { // bouton gauche
            ActiverDeplacementSouris();
        } else if (event.button == 2) {
            DesactiverDeplacementSouris();
            controleur.donnee.ReinitParametreAffichage();
            controleur.actualiser();
        }
    }, { passive: false });

    image.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });

    image.addEventListener('mouseenter', () => {
        sourisSurGraphe = true;
    });

    image.addEventListener('mouseleave', () => {
        sourisSurGraphe = false;
    });

    document.addEventListener('mouseup', DesactiverDeplacementSouris);

    document.addEventListener('mousemove', EffectuerDeplacementSouris);

    window.addEventListener('wheel', function (event) {
        if (sourisSurGraphe) {
            event.preventDefault();

            const deltaY = event.deltaY;

            var taille = 0;

            if (deltaY > 0) {
                taille = 0.9;
            } else if (deltaY < 0) {
                taille = 1.1;
            }

            let reticule = controleur.generateur.ReticuleDonnee;

            let mouseX = reticule.x // à définir selon ton événement souris
            let mouseY = reticule.y;

            let ancienScaleX = controleur.donnee.ScaleX;
            let ancienScaleY = controleur.donnee.ScaleY;

            // Position dans le monde avant zoom
            let sourisGrapheX = (mouseX - controleur.donnee.DeplacementXPixel) / ancienScaleX;
            let sourisGrapheY = (mouseY - controleur.donnee.DeplacementYPixel) / ancienScaleY;

            // Appliquer zoom
            let nouvelleScaleX = ancienScaleX * taille;
            let nouvelleScaleY = ancienScaleY * taille;
            controleur.donnee.DefinirScale(nouvelleScaleX, nouvelleScaleY);

            // Recalculer le déplacement pour garder la souris fixe
            let nouveauDeplacementX = mouseX - sourisGrapheX * nouvelleScaleX;
            let nouveauDeplacementY = mouseY - sourisGrapheY * nouvelleScaleY;
            controleur.donnee.DefinirDeplacement(nouveauDeplacementX, nouveauDeplacementY);

            controleur.actualiser();
        }
    }, { passive: false });
}