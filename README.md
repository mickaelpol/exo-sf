# exo-sf

### Installation du projet

> git clone git@github.com:mickaelpol/exo-sf.git

> composer install

> sudo apt-get update

> sudo apt-get install yarn

> yarn install

> yarn build (Pour compiler les fichiers et les minifier)

> Ne pas oublier de faire un CHMOD 777 -R /public ( sinon l'accès au service worker ne fonctionnera pas )

> php bin/console server:start



### Points à faire

- [x] Formater les données récupérées par le service afin que le tableau en retour renvoi de vraies valeurs
- [x] Passer les données au template via le controller
- [x] Mettre à jour le template pour afficher les données
- [x] Remonter la météo de toulouse en Occitanie
- [x] Gérer les erreurs de l'API via un bloc `try catch` dans le service
- [x] Ajouter un formulaire et des appels API pour permettre à l'utilisateur de choisir sa ville
- [x] Faire de la page qui affiche la météo la home page
- [x] Rendre la page qui affiche la météo responsive
