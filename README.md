# HEIG-VD DévProdMéd Course - Mini-projet

Ce dépôt contient le mini-projet à réaliser dans le cadre du cours
_"[Développement de produit média (DévProdMéd)](https://github.com/heig-vd-devprodmed-course/heig-vd-devprodmed-course)"_
enseigné à la
[Haute Ecole d'Ingénierie et de Gestion du Canton de Vaud (HEIG-VD)](https://heig-vd.ch),
Suisse.
L'application est accessible sur http://127.0.0.1:8000

## Choix techniques

- **Deux applications Vue.js séparées** : une pour le dashboard, une pour la page de votes, car les deux pages sont indépendantes et n'on pas besoin de partager d'état
- **Store `usePollStore`** : centralise toutes les opérations sur les sondages (liste, création, modification, suppression), ca permet de ne pas dupliquer la logique dans les compsants
- **Composable `usePolling`** : utilisé pour rafraîchir les résultats toutes les 5 secondes, c'est la solution la plus simple pour afficher les résultats en temps réel.
- **Graphique en barres CSS** : sans librairie externe, chaque barre est une div dont la largeur est calculée en pourcentage selon le nombre de vote
- **Un seul contrôleur API `ApiPollController`** : regroupe toutes les opérations sur les sondages pour garder le backend simple et lisible.

## Utilisation de l'IA

L'IA a été utilisée comme assistant tout au long du développement, notamment pour débuguer et structurer l'architecture ou parfois générer du code (par exemple pour le graphique car je ne savais pas comment m'y prendre). 
Mais quoi qu'il en soit, ce sur quoi l'ia m'a aidé a été compris, relu et validé avant d'être intégrée au projet.
