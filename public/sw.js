const absoluteUrl = self.location.href;

const cacheName = 'data-city';

const cacheAssets = [
    '/data-city',
    `${absoluteUrl}/build/images/ensoleille.6d81e345.jpg`

]

/* Appel de l'event "Install" */
self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(cacheName)
            .then(cache => cache.addAll(cacheAssets))
            .catch(error => console.error('Error: ', error))
    )
})

/* Appel de l'event "Activated" */
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== cacheName) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

/* Ecoute des requetes serveur afin de mettre en cache les données récupéré
*  Si aucune données n'est disponible en cache on récupère les données sur le reseau
* et ont les stocke en cache par la même occasion
*/
self.addEventListener('fetch', function (event) {
    event.respondWith(
        caches.match(event.request).then(function (response) {
            if (response) {
                return response;
            }
            return fetch(event.request).then(function (response) {
                return response;
            }).catch(function (error) {
                console.error('Récupération échouée:', error);

                throw error;
            });
        })
    );
});

