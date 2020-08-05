/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

/*
* Import des librairies et fichiers :
* jquery-ui
* app.css
* jquery
* jquery-ui
* bootstrap
*/
import '../../node_modules/jquery-ui-dist/jquery-ui.css'
import '../css/app.css';

import '../../node_modules/jquery/dist/jquery';
import '../../node_modules/jquery-ui-dist/jquery-ui';
import '../../node_modules/bootstrap/dist/js/bootstrap.bundle';


import $ from 'jquery';

/*
* Lancement des fonction de jquery à la fin du chargement du DOM de la page
*/
$(document).ready(function () {

    if('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register('sw.js')
            .then(function() { console.log('Service Worker Registered'); });
    }

    /*
     * Variable contenant la classe de l'input de recherche
    */
    let searchInput = $('.search_input');
    searchInput.val('');

    let form = $('#city_search');
    let icon = $('.search_icon');
    let city_id = $('.city_id');
    let city_lat = $('.city_lat');
    let city_lon = $('.city_lon');
    /*
    * Url du controller traitant la requêtes sur la liste des villes
    */
    const url = '/data-city';
    /*
    * Instanciation du tableau qui contiendra les données des villes formatté
    */
    let cities = [];

    /*
    * Fonction qui écoute l'événement de soumission du formulaire
    * pour attribuer la valeur du data-id de l'input principal
    * à l'input caché
    */
    function keyPressEnter(formId, cityId, cityLat, cityLon, input) {
        formId.submit(function (e) {
            cityId.val(input.data('id'))
            cityLat.val(input.data('lat'))
            cityLon.val(input.data('lon'))
            formId.submit();
        });
    }

    /*
    * Lancement de la fonction
    */
    keyPressEnter(form, city_id, city_lat, city_lon, searchInput);

    /*
     * Affichage des popover de bootstrap
     * pour afficher une description des icones en dessous de la température
    */
    $('.pop').popover({
        trigger: "hover",
    });

    /* Requête sur le fichier json stocké sur le serveur pour récupérer la liste des villes
     * pour les formatter et récupérer l'id, le nom, le code national la latitude et longitude
     * afin d'effectuer une requête plus précise sur openweathermap
    */
    let headers = new Headers();
    headers.append('pragma', 'cache');
    headers.append('cache-control', 'cache');
    let init = {
        method: 'GET',
        headers: headers,
        mode: 'same-origin',
    };

    fetch(url, init)
        .then(results =>  results.json())
        .then(function (data) {
            /*
             * Parcours du tableau contenant les villes afin de les formatters
            */
            for (let i = 0; i < data.length; i++) {
                let id = data[i]['id'];
                let name = data[i]['name'];
                let cn = data[i]['country'];
                let lat = data[i]['coord']['lat'];
                let lon = data[i]['coord']['lon'];
                /* Création du formattage des données des villes */
                let city = {
                    'label': `${name} ,${cn}`,
                    'value': `${name},${cn}`,
                    'id': id,
                    'lat': `${lat}`,
                    'lon': `${lon}`
                };
                /* Ajout des villes dans le tableau cities instancier plus haut */
                cities.push(city)
            }
            return cities;
        })
        .catch( (err) => {
            console.error('ERROR', err.message);
        });

    /*
    * Methode de jquery-ui autocomplete
    * Puis trie des données afin de trouver les villes correspondante sur le fichier json
    * à la recherche utilisateur
    */
    searchInput.autocomplete({
        minLength: 3, // Min 3 caractères afin de lancer la recherche des villes correspondante au caractères tapés
        source: function (request, response, e) {
            let filteredArray = $.map(cities, function (item) {
                if (!item.value.toLowerCase().startsWith(request.term)) {
                    return null;
                } else {
                    return item;
                }
            })
            response(filteredArray.slice(0, 5))
        },
        /* J'attribue ici les data id latitude longitude à un input caché en dessous de la barre de recherche
          * afin de l'envoyer vers le controlleur WeatherController utilisant le service WeatherService
         */
        select: function (event, ui) {
            searchInput.attr("data-id", ui.item.id);
            searchInput.attr('data-lat', ui.item.lat);
            searchInput.attr('data-lon', ui.item.lon);
        },
    });
})
