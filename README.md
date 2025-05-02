## Fonctionnalités prioritaires du MVP

# 1. Gestion des utilisateurs

- Inscription/connexion avec email/mot de passe
- Validation du compte par email
- Profil utilisateur avec photo (optionnelle)

# 2. Voyages collaboratifs

- Création et gestion de voyages
- Invitation des membres par email
- Organisation par dates (optionnelles) et lieu
- Vue des voyages à venir/passés avec filtre et tri

# 3. Gestion des éléments du voyage

- Logements: ajout, visualisation sur carte, détails
- Transports: ajout, visualisation sur carte, détails
- Activités: ajout, visualisation sur carte, détails

# 4. Planning

- Vue calendrier des logements/transports/activités
- Filtrage par type d'élément et par membre



# todo list : 

## Gestion argent 

- Possible sur chaque élément d'ajouter une dépense
- Possible d'ajouter une dépense non liée a un événement 
- Sur chaque dépense, définir qui a payé et pour qui 
- Faire un graphique pour montrer qui dois combien a qui 
- Ajouter une notion d'objectif de budget

## Activités 

- Système de vote : 
    - A la création de l'activité, possibilité de choisir si on souhaite soumettre au vote ou non 
    - Les différents utilisateurs peuvent voter pour les activités qui les intéressent (dans une section dédiée sur la page voyage)
    - Tous les membres du voyage ont les memes droits, ils peuvent tous passer une activité de vote a validée et modifier la liste de membres
- Ajout d'un statut (A réserver, réservé, payé, etc)
- Ajout de plusieurs images de l'activité
- Une fois l'activité plus en phase de vote, il faut pouvoir choisir la date et l'heure
- Pouvoir lier des membres a une activité

## Utilisateur 

- Possible de mettre une photo de profil

## Logements 

## Transports 

## Coffre fort numérique

- Séparer en deux sections: 
    - des documents a ajouter au global sur mon profil (passport, CNI, livret de famille, etc) -> Se renseigner sur la legislation du stockage 
    - Des documents relatifs au voyage (billet d'avion, de train, entrée dans un musée, etc)
- avoir une section ou je peux retrouver ces differents documents

## Voyage

- Notion d'historique des modifications pour voir qui a fait quoi
- Ajouter un système de sondage pour la date du voyage


## Autre 
- Gestion des relations suppression activité
- landing page
- traduction correcte code de base 
- gestion delete cascade ou non table travel users
- page erreur au lieu du 404 invitation
- ajouter queues
- setup mail sur la prod
- cleanup des photos sur le s3 a la suppression des resources qui en ont
