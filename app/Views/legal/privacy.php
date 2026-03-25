<div class="legal-content">
    <div class="legal-header">
        <h1><i class="fas fa-shield-alt"></i> Politique de Confidentialité (RGPD)</h1>
        <p class="text-muted">Dernière mise à jour : 14/11/2025</p>
    </div>

    <div class="legal-section">
        <h2>1. Introduction</h2>
        <p>
            La présente Politique de confidentialité décrit la façon dont <strong>SBN v1.0 (Synology Backup Notifier)</strong>
            collecte, utilise, stocke et protège vos données personnelles, conformément au Règlement Général sur la
            Protection des Données (RGPD - Règlement UE 2016/679) et à la loi française Informatique et Libertés.
        </p>
        <p>
            <strong>Soon22</strong> s'engage à respecter la vie privée de ses utilisateurs et à protéger leurs données
            personnelles. Cette politique s'applique à tous les utilisateurs de la plateforme SBN, qu'ils soient administrateurs,
            utilisateurs, collaborateurs ou clients.
        </p>
    </div>

    <div class="legal-section">
        <h2>2. Responsable du Traitement</h2>
        <p><strong>Raison sociale :</strong> Soon22 - Johnny Girault</p>
        <p><strong>Adresse :</strong> 84 Bis Rue Prosper Lissagaray, 59200 Tourcoing, France</p>
        <p><strong>Email :</strong> <a href="mailto:contact@soon22.fr">contact@soon22.fr</a></p>
        <p><strong>Téléphone :</strong> +33 6 49 23 08 90</p>
        <p><strong>Site web :</strong> <a href="https://soon22.fr" target="_blank">https://soon22.fr</a></p>
        <p><strong>SIRET :</strong> [À compléter si applicable]</p>

        <h3>2.1. Hébergement des Données</h3>
        <p><strong>Hébergeur :</strong> O2Switch</p>
        <p><strong>Adresse :</strong> Chemin des Pardiaux, 63000 Clermont-Ferrand, France</p>
        <p><strong>Site web :</strong> <a href="https://www.o2switch.fr" target="_blank">https://www.o2switch.fr</a></p>
        <p><strong>Certification :</strong> Infrastructure sécurisée située en France, conforme RGPD</p>
        <p>
            Les données sont hébergées exclusivement sur le territoire français, garantissant ainsi le respect
            de la législation européenne en matière de protection des données.
        </p>
    </div>

    <div class="legal-section">
        <h2>3. Données Collectées</h2>

        <h3>3.1. Données d'Identification</h3>
        <ul>
            <li><strong>Nom et prénom</strong> : pour personnaliser votre compte</li>
            <li><strong>Adresse email</strong> : pour l'authentification et les notifications</li>
            <li><strong>Nom de la société</strong> : pour la gestion multi-tenant</li>
            <li><strong>Numéro de téléphone</strong> : optionnel, pour les alertes SMS</li>
            <li><strong>Mot de passe chiffré</strong> : stocké via algorithme bcrypt (irréversible)</li>
            <li><strong>Rôle utilisateur</strong> : admin, user, collaborator, client</li>
        </ul>

        <h3>3.2. Données de Connexion et de Sécurité</h3>
        <ul>
            <li><strong>Adresse IP</strong> : pour la sécurité et la prévention des fraudes</li>
            <li><strong>User agent</strong> : navigateur et système d'exploitation</li>
            <li><strong>Date et heure de connexion</strong> : pour l'audit trail</li>
            <li><strong>Tentatives de connexion échouées</strong> : pour détecter les attaques</li>
            <li><strong>Tokens API</strong> : pour l'intégration avec les NAS Synology</li>
            <li><strong>Tokens CSRF</strong> : pour la protection contre les attaques CSRF</li>
            <li><strong>Sessions actives</strong> : pour la gestion des connexions multiples</li>
        </ul>

        <h3>3.3. Données de Monitoring des Sauvegardes</h3>
        <ul>
            <li><strong>Informations sur les sauvegardes</strong> : dates, heures, durées, statuts (succès/échec)</li>
            <li><strong>Informations sur les appareils</strong> : noms des NAS, modèles, versions DSM</li>
            <li><strong>Informations sur les clients sauvegardés</strong> : noms, types (PC, serveurs, VM)</li>
            <li><strong>Métadonnées techniques</strong> : tailles, types de sauvegardes, chemins</li>
            <li><strong>Historique des sauvegardes</strong> : jusqu'à 90 jours par défaut</li>
            <li><strong>Logs des webhooks</strong> : appels d'API reçus depuis les NAS</li>
        </ul>

        <h3>3.4. Données d'Abonnement</h3>
        <ul>
            <li><strong>Statut d'abonnement</strong> : trial, active, expired, suspended</li>
            <li><strong>Dates de début et fin</strong> : période de validité</li>
            <li><strong>Historique des modifications</strong> : jours offerts, suspensions, avec raisons</li>
            <li><strong>Informations de paiement</strong> : non stockées (gérées par le prestataire de paiement tiers)</li>
        </ul>

        <h3>3.5. Cookies et Traceurs</h3>
        <ul>
            <li><strong>Cookies de session (PHPSESSID)</strong> : essentiels, authentification (supprimé à la déconnexion)</li>
            <li><strong>Cookies CSRF</strong> : essentiels, protection contre les attaques (durée de session)</li>
            <li><strong>Cookie "Remember Me"</strong> : optionnel, connexion automatique (30 jours)</li>
            <li><strong>Cookies de préférence</strong> : optionnel, langue, thème (1 an)</li>
        </ul>
        <p>
            Aucun cookie publicitaire ou de tracking tiers n'est utilisé. Tous les cookies sont émis par SBN uniquement
            pour le fonctionnement du service.
        </p>
    </div>

    <div class="legal-section">
        <h2>4. Finalités du Traitement</h2>
        <p>Vos données personnelles sont collectées et traitées pour les finalités suivantes :</p>

        <h3>4.1. Gestion du Service</h3>
        <ul>
            <li><strong>Création et gestion de compte</strong> : inscription, connexion, profil utilisateur</li>
            <li><strong>Authentification et sécurité</strong> : vérification d'identité, prévention des fraudes</li>
            <li><strong>Monitoring des sauvegardes</strong> : réception et affichage des statuts de sauvegardes</li>
            <li><strong>Gestion multi-tenant</strong> : isolation des données par société</li>
            <li><strong>Partage d'accès</strong> : permettre le partage de dashboards avec collaborateurs/clients</li>
        </ul>

        <h3>4.2. Communication</h3>
        <ul>
            <li><strong>Notifications de sauvegardes</strong> : alertes en cas d'échec ou de succès</li>
            <li><strong>Notifications de compte</strong> : changement de mot de passe, expiration d'abonnement</li>
            <li><strong>Support client</strong> : réponse aux demandes d'assistance</li>
            <li><strong>Mises à jour importantes</strong> : changements de CGU, nouvelles fonctionnalités</li>
        </ul>

        <h3>4.3. Amélioration du Service</h3>
        <ul>
            <li><strong>Analyse d'utilisation</strong> : statistiques anonymes sur l'usage de la plateforme</li>
            <li><strong>Détection de bugs</strong> : logs d'erreurs pour améliorer la stabilité</li>
            <li><strong>Optimisation</strong> : performance et expérience utilisateur</li>
        </ul>

        <h3>4.4. Obligations Légales</h3>
        <ul>
            <li><strong>Conservation des logs</strong> : conformément à la législation française (1 an)</li>
            <li><strong>Réponse aux autorités</strong> : en cas de réquisition judiciaire</li>
            <li><strong>Comptabilité</strong> : factures et historique de paiements (10 ans)</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>5. Base Légale du Traitement</h2>
        <p>Conformément à l'article 6 du RGPD, le traitement de vos données repose sur :</p>
        <ul>
            <li>
                <strong>Consentement (Art. 6.1.a)</strong> : donné lors de l'inscription et acceptation des CGU.
                Vous pouvez retirer votre consentement à tout moment via la suppression de votre compte.
            </li>
            <li>
                <strong>Exécution du contrat (Art. 6.1.b)</strong> : nécessaire pour fournir le service de monitoring
                des sauvegardes que vous avez souscrit.
            </li>
            <li>
                <strong>Intérêt légitime (Art. 6.1.f)</strong> : sécurité de la plateforme, prévention de la fraude,
                amélioration du service.
            </li>
            <li>
                <strong>Obligation légale (Art. 6.1.c)</strong> : conservation des logs de connexion (LCEN),
                conservation des factures (Code de commerce).
            </li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>6. Durée de Conservation des Données</h2>
        <table class="legal-table">
            <thead>
                <tr>
                    <th>Type de données</th>
                    <th>Durée de conservation</th>
                    <th>Base légale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Données de compte actif</td>
                    <td>Jusqu'à suppression du compte</td>
                    <td>Exécution du contrat</td>
                </tr>
                <tr>
                    <td>Données de compte supprimé</td>
                    <td>Anonymisation immédiate</td>
                    <td>RGPD</td>
                </tr>
                <tr>
                    <td>Logs de connexion</td>
                    <td>12 mois</td>
                    <td>Obligation légale (LCEN)</td>
                </tr>
                <tr>
                    <td>Historique des sauvegardes</td>
                    <td>90 jours (configurable)</td>
                    <td>Exécution du contrat</td>
                </tr>
                <tr>
                    <td>Tokens API révoqués</td>
                    <td>Suppression immédiate</td>
                    <td>Sécurité</td>
                </tr>
                <tr>
                    <td>Factures et paiements</td>
                    <td>10 ans</td>
                    <td>Obligation légale (comptabilité)</td>
                </tr>
                <tr>
                    <td>Cookies de session</td>
                    <td>Fin de session (déconnexion)</td>
                    <td>Fonctionnement technique</td>
                </tr>
                <tr>
                    <td>Cookie "Remember Me"</td>
                    <td>30 jours maximum</td>
                    <td>Consentement utilisateur</td>
                </tr>
            </tbody>
        </table>
        <p class="mt-20">
            <strong>Note :</strong> À l'expiration des durées indiquées, les données sont automatiquement supprimées
            ou anonymisées de manière irréversible.
        </p>
    </div>

    <div class="legal-section">
        <h2>7. Vos Droits RGPD</h2>
        <p>
            Conformément aux articles 15 à 22 du RGPD, vous disposez des droits suivants concernant vos données personnelles :
        </p>

        <h3>7.1. Droit d'Accès (Art. 15)</h3>
        <p>
            Vous avez le droit d'obtenir la confirmation que des données vous concernant sont traitées, et d'accéder à ces données.
            Vous pouvez exporter l'intégralité de vos données au format JSON via <a href="<?= APP_URL ?>/gdpr/export">cette page</a>.
        </p>

        <h3>7.2. Droit de Rectification (Art. 16)</h3>
        <p>
            Vous pouvez modifier vos informations personnelles (nom, prénom, email, téléphone) directement depuis
            <a href="<?= APP_URL ?>/settings/profile">votre profil</a>.
        </p>

        <h3>7.3. Droit à l'Effacement / "Droit à l'oubli" (Art. 17)</h3>
        <p>
            Vous pouvez demander la suppression complète de votre compte et de toutes vos données via
            <a href="<?= APP_URL ?>/gdpr/delete">cette page</a>. Cette action est <strong>irréversible</strong>.
        </p>
        <p>
            <strong>Attention :</strong> Les données soumises à une obligation légale de conservation (factures, logs de sécurité)
            seront anonymisées mais conservées pour la durée légale requise.
        </p>

        <h3>7.4. Droit à la Portabilité (Art. 20)</h3>
        <p>
            Vous pouvez récupérer vos données dans un format structuré, couramment utilisé et lisible par machine (JSON).
            Utilisez la fonction <a href="<?= APP_URL ?>/gdpr/export">d'export de données</a>.
        </p>

        <h3>7.5. Droit d'Opposition (Art. 21)</h3>
        <p>
            Vous pouvez vous opposer au traitement de vos données à des fins de prospection commerciale.
            Soon22 n'effectuant aucune prospection commerciale automatisée, ce droit s'applique principalement
            à la désactivation des notifications non essentielles.
        </p>

        <h3>7.6. Droit à la Limitation du Traitement (Art. 18)</h3>
        <p>
            Vous pouvez demander la limitation du traitement de vos données dans certains cas (contestation de l'exactitude,
            traitement illicite, etc.). Contactez-nous à <a href="mailto:contact@soon22.fr">contact@soon22.fr</a>.
        </p>

        <h3>7.7. Droit de ne pas faire l'objet d'une Décision Automatisée (Art. 22)</h3>
        <p>
            Soon22 n'utilise aucun système de prise de décision automatisée ou de profilage concernant vos données personnelles.
        </p>

        <h3>7.8. Exercer Vos Droits</h3>
        <div class="legal-box">
            <p><strong>Méthodes pour exercer vos droits :</strong></p>
            <ul>
                <li>
                    <strong>Interface web</strong> :
                    <ul>
                        <li>Export de données : <a href="<?= APP_URL ?>/gdpr/export">Cliquez ici</a></li>
                        <li>Suppression de compte : <a href="<?= APP_URL ?>/gdpr/delete">Cliquez ici</a></li>
                        <li>Modification de profil : <a href="<?= APP_URL ?>/settings/profile">Cliquez ici</a></li>
                    </ul>
                </li>
                <li>
                    <strong>Par email</strong> : <a href="mailto:contact@soon22.fr">contact@soon22.fr</a><br>
                    Objet : "Demande RGPD - [Votre nom]"<br>
                    Joindre une copie de pièce d'identité pour vérification
                </li>
                <li>
                    <strong>Par courrier</strong> :<br>
                    Soon22 - Johnny Girault<br>
                    84 Bis Rue Prosper Lissagaray<br>
                    59200 Tourcoing, France
                </li>
            </ul>
            <p>
                <strong>Délai de réponse :</strong> 1 mois maximum à compter de la réception de votre demande
                (prolongeable de 2 mois en cas de complexité).
            </p>
        </div>

        <h3>7.9. Droit de Réclamation auprès de la CNIL</h3>
        <p>
            Si vous estimez que le traitement de vos données personnelles constitue une violation du RGPD,
            vous avez le droit d'introduire une réclamation auprès de la Commission Nationale de l'Informatique
            et des Libertés (CNIL) :
        </p>
        <p>
            <strong>CNIL</strong><br>
            3 Place de Fontenoy - TSA 80715<br>
            75334 Paris Cedex 07<br>
            Téléphone : 01 53 73 22 22<br>
            Site web : <a href="https://www.cnil.fr" target="_blank">https://www.cnil.fr</a>
        </p>
    </div>

    <div class="legal-section">
        <h2>8. Sécurité des Données</h2>
        <p>
            Soon22 met en œuvre toutes les mesures techniques et organisationnelles appropriées pour protéger
            vos données personnelles contre la destruction, la perte, l'altération, la divulgation non autorisée
            ou l'accès non autorisé.
        </p>

        <h3>8.1. Mesures Techniques</h3>
        <ul>
            <li><strong>Chiffrement des mots de passe</strong> : algorithme bcrypt avec salt aléatoire (irréversible)</li>
            <li><strong>Connexions HTTPS/TLS</strong> : chiffrement de toutes les communications (certificat SSL)</li>
            <li><strong>Tokens API sécurisés</strong> : génération aléatoire cryptographiquement sûre (32 caractères)</li>
            <li><strong>Protection contre les injections SQL</strong> : requêtes préparées (prepared statements)</li>
            <li><strong>Protection XSS</strong> : échappement de toutes les sorties HTML</li>
            <li><strong>Protection CSRF</strong> : tokens anti-CSRF sur tous les formulaires</li>
            <li><strong>Limitation des tentatives de connexion</strong> : blocage temporaire après 5 échecs</li>
            <li><strong>Sauvegardes chiffrées</strong> : backup quotidien de la base de données (AES-256)</li>
            <li><strong>Logs de sécurité</strong> : journalisation de tous les accès et actions sensibles</li>
            <li><strong>Isolation des données</strong> : architecture multi-tenant avec séparation stricte par company_id</li>
        </ul>

        <h3>8.2. Mesures Organisationnelles</h3>
        <ul>
            <li><strong>Accès restreint</strong> : seul le personnel autorisé accède aux données (DPO + développeur)</li>
            <li><strong>Confidentialité</strong> : engagement de confidentialité signé par le personnel</li>
            <li><strong>Surveillance</strong> : monitoring 24/7 des accès et des tentatives d'intrusion</li>
            <li><strong>Mises à jour régulières</strong> : patch de sécurité appliqués sous 48h</li>
            <li><strong>Audits périodiques</strong> : revue de sécurité trimestrielle</li>
            <li><strong>Plan de réponse aux incidents</strong> : procédure en cas de violation de données</li>
        </ul>

        <h3>8.3. En Cas de Violation de Données</h3>
        <p>
            Conformément à l'article 33 du RGPD, en cas de violation de données personnelles susceptible d'engendrer
            un risque élevé pour vos droits et libertés, Soon22 s'engage à :
        </p>
        <ul>
            <li><strong>Notifier la CNIL</strong> dans les 72 heures suivant la découverte de la violation</li>
            <li><strong>Vous informer</strong> directement par email dans les meilleurs délais</li>
            <li><strong>Décrire</strong> la nature de la violation, les données concernées, les mesures prises</li>
            <li><strong>Mettre en place</strong> des mesures correctives pour éviter toute récurrence</li>
        </ul>
    </div>

    <div class="legal-section">
        <h2>9. Partage et Transfert de Données</h2>

        <h3>9.1. Partage Interne</h3>
        <p>
            Vos données de sauvegarde peuvent être consultées par :
        </p>
        <ul>
            <li><strong>Les utilisateurs de votre société</strong> : selon leur rôle (admin, user, collaborator)</li>
            <li><strong>Les collaborateurs avec qui vous partagez l'accès</strong> : lecture/écriture selon les permissions</li>
            <li><strong>Les clients avec accès partagé</strong> : lecture seule de leur propre dashboard uniquement</li>
        </ul>
        <p>
            Cette fonctionnalité de partage est essentielle au service et repose sur votre consentement explicite lors de la création du partage.
        </p>

        <h3>9.2. Partage avec des Tiers</h3>
        <p><strong>Soon22 ne vend JAMAIS vos données personnelles à des tiers.</strong></p>
        <p>
            Les seuls tiers susceptibles d'accéder à vos données sont :
        </p>
        <ul>
            <li>
                <strong>O2Switch (hébergeur)</strong> : stockage sécurisé des données en France.
                O2Switch est conforme RGPD et ne consulte jamais les données hébergées.
            </li>
            <li>
                <strong>Prestataire de paiement</strong> : pour le traitement des abonnements payants
                (vos informations bancaires ne transitent JAMAIS par nos serveurs).
            </li>
            <li>
                <strong>Autorités légales</strong> : uniquement en cas de réquisition judiciaire ou d'obligation légale.
            </li>
        </ul>

        <h3>9.3. Transferts Internationaux</h3>
        <p>
            <strong>Aucun transfert hors de l'Union Européenne.</strong>
        </p>
        <p>
            Toutes vos données sont hébergées en France (O2Switch, Clermont-Ferrand) et ne font jamais l'objet
            de transferts vers des pays tiers. Cela garantit le respect du RGPD et des standards européens de protection des données.
        </p>
    </div>

    <div class="legal-section">
        <h2>10. Cookies et Technologies Similaires</h2>

        <h3>10.1. Types de Cookies Utilisés</h3>
        <table class="legal-table">
            <thead>
                <tr>
                    <th>Cookie</th>
                    <th>Type</th>
                    <th>Finalité</th>
                    <th>Durée</th>
                    <th>Consentement requis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHPSESSID</td>
                    <td>Session</td>
                    <td>Authentification utilisateur</td>
                    <td>Fin de session</td>
                    <td>❌ Essentiel</td>
                </tr>
                <tr>
                    <td>csrf_token</td>
                    <td>Sécurité</td>
                    <td>Protection CSRF</td>
                    <td>Fin de session</td>
                    <td>❌ Essentiel</td>
                </tr>
                <tr>
                    <td>remember_me</td>
                    <td>Fonctionnel</td>
                    <td>Connexion automatique</td>
                    <td>30 jours</td>
                    <td>✅ Optionnel</td>
                </tr>
                <tr>
                    <td>user_preferences</td>
                    <td>Fonctionnel</td>
                    <td>Langue, thème</td>
                    <td>1 an</td>
                    <td>✅ Optionnel</td>
                </tr>
            </tbody>
        </table>

        <h3>10.2. Gestion des Cookies</h3>
        <p>
            Vous pouvez gérer vos préférences de cookies via les paramètres de votre navigateur :
        </p>
        <ul>
            <li><strong>Chrome</strong> : Paramètres → Confidentialité et sécurité → Cookies</li>
            <li><strong>Firefox</strong> : Préférences → Vie privée et sécurité → Cookies</li>
            <li><strong>Safari</strong> : Préférences → Confidentialité → Cookies</li>
            <li><strong>Edge</strong> : Paramètres → Cookies et autorisations de site</li>
        </ul>
        <p>
            <strong>Attention :</strong> Le blocage des cookies essentiels (PHPSESSID, csrf_token) empêchera le fonctionnement
            de la plateforme et rendra impossible toute connexion.
        </p>

        <h3>10.3. Absence de Cookies Publicitaires</h3>
        <p>
            Soon22 n'utilise <strong>aucun cookie publicitaire, tracking, analytics tiers (Google Analytics, Facebook Pixel, etc.)</strong>.
            Seuls les cookies strictement nécessaires au fonctionnement du service sont déposés.
        </p>
    </div>

    <div class="legal-section">
        <h2>11. Protection des Données des Mineurs</h2>
        <p>
            Le service SBN est destiné à un usage professionnel et n'est pas conçu pour être utilisé par des personnes
            de moins de 16 ans (âge du consentement numérique en France selon l'article 8 du RGPD).
        </p>
        <p>
            Si vous avez connaissance qu'un mineur de moins de 16 ans a créé un compte sans autorisation parentale,
            veuillez nous contacter immédiatement à <a href="mailto:contact@soon22.fr">contact@soon22.fr</a>.
            Le compte sera supprimé dans les 48 heures.
        </p>
        <p>
            Pour les mineurs entre 16 et 18 ans, une autorisation parentale est fortement recommandée.
        </p>
    </div>

    <div class="legal-section">
        <h2>12. Analyses et Statistiques</h2>
        <p>
            Soon22 collecte des statistiques d'utilisation anonymes pour améliorer le service :
        </p>
        <ul>
            <li>Nombre de connexions (sans identité)</li>
            <li>Pages les plus consultées</li>
            <li>Temps de réponse du serveur</li>
            <li>Erreurs techniques rencontrées</li>
        </ul>
        <p>
            <strong>Important :</strong> Ces statistiques sont collectées de manière <strong>anonyme</strong> et ne permettent
            pas de vous identifier personnellement. Aucun outil tiers (Google Analytics, etc.) n'est utilisé.
        </p>
    </div>

    <div class="legal-section">
        <h2>13. Notifications et Communications</h2>

        <h3>13.1. Notifications Essentielles (non désactivables)</h3>
        <ul>
            <li>Échec critique de sauvegarde</li>
            <li>Expiration imminente d'abonnement (7 jours avant)</li>
            <li>Changement de mot de passe</li>
            <li>Connexion depuis un nouvel appareil</li>
            <li>Modifications des CGU ou de la politique de confidentialité</li>
        </ul>

        <h3>13.2. Notifications Optionnelles (désactivables)</h3>
        <ul>
            <li>Succès de chaque sauvegarde</li>
            <li>Rapport hebdomadaire des sauvegardes</li>
            <li>Nouvelles fonctionnalités</li>
            <li>Conseils d'utilisation</li>
        </ul>
        <p>
            Gérez vos préférences de notifications dans <a href="<?= APP_URL ?>/settings/notifications">vos paramètres</a>.
        </p>

        <h3>13.3. Désinscription</h3>
        <p>
            Vous pouvez vous désabonner des notifications optionnelles à tout moment via le lien présent dans chaque email
            ou dans <a href="<?= APP_URL ?>/settings/notifications">vos paramètres</a>.
        </p>
    </div>

    <div class="legal-section">
        <h2>14. Sous-traitants et Responsabilité</h2>
        <p>
            Soon22 fait appel aux sous-traitants suivants pour le traitement de vos données :
        </p>
        <ul>
            <li>
                <strong>O2Switch (hébergement)</strong> : stockage des données en France.
                <a href="https://www.o2switch.fr/mentions-legales/" target="_blank">Politique de confidentialité O2Switch</a>
            </li>
        </ul>
        <p>
            Tous les sous-traitants ont signé un accord de traitement des données (DPA - Data Processing Agreement)
            conforme à l'article 28 du RGPD, garantissant un niveau de protection équivalent.
        </p>
    </div>

    <div class="legal-section">
        <h2>15. Modifications de la Politique de Confidentialité</h2>
        <p>
            Soon22 se réserve le droit de modifier cette Politique de confidentialité à tout moment pour refléter :
        </p>
        <ul>
            <li>Les évolutions légales et réglementaires</li>
            <li>Les nouvelles fonctionnalités du service</li>
            <li>Les recommandations de la CNIL</li>
        </ul>
        <p>
            En cas de modification substantielle, vous serez informé par :
        </p>
        <ul>
            <li><strong>Email</strong> : notification 30 jours avant l'entrée en vigueur</li>
            <li><strong>Bannière</strong> : notification dans l'interface SBN</li>
            <li><strong>Cette page</strong> : date de mise à jour indiquée en haut</li>
        </ul>
        <p>
            La poursuite de l'utilisation du service après l'entrée en vigueur des modifications vaut acceptation de la nouvelle politique.
            Si vous refusez les modifications, vous pouvez supprimer votre compte via <a href="<?= APP_URL ?>/gdpr/delete">cette page</a>.
        </p>
    </div>

    <div class="legal-section">
        <h2>16. Disclaimer Synology</h2>
        <p>
            <strong>SBN (Synology Backup Notifier) est une solution indépendante développée par Soon22.</strong>
        </p>
        <p>
            Ce service n'est pas affilié à, sponsorisé par, ou associé de quelque manière que ce soit à <strong>Synology Inc.</strong>.
            Synology®, DiskStation®, Active Backup™, DSM™ sont des marques déposées de Synology Inc.
        </p>
        <p>
            Soon22 ne garantit pas la compatibilité avec toutes les versions de DSM ou Active Backup for Business.
            L'utilisation de SBN avec les produits Synology se fait sous votre propre responsabilité.
        </p>
    </div>

    <div class="legal-section">
        <h2>17. Contact et Délégué à la Protection des Données (DPO)</h2>
        <p>
            Pour toute question concernant cette Politique de confidentialité ou l'exercice de vos droits RGPD :
        </p>
        <div class="legal-box">
            <p><strong>Délégué à la Protection des Données :</strong></p>
            <p>
                <strong>Nom :</strong> Johnny Girault (Soon22)<br>
                <strong>Email :</strong> <a href="mailto:contact@soon22.fr">contact@soon22.fr</a><br>
                <strong>Téléphone :</strong> +33 6 49 23 08 90<br>
                <strong>Adresse postale :</strong><br>
                Soon22<br>
                84 Bis Rue Prosper Lissagaray<br>
                59200 Tourcoing<br>
                France
            </p>
            <p><strong>Délai de réponse :</strong> 1 mois maximum (extensible à 3 mois en cas de complexité)</p>
        </div>
    </div>

    <div class="legal-section">
        <h2>18. Acceptation de la Politique</h2>
        <p>
            L'utilisation du service SBN implique l'acceptation pleine et entière de cette Politique de confidentialité.
        </p>
        <p>
            Si vous n'acceptez pas les termes de cette politique, veuillez ne pas utiliser le service et supprimer votre compte.
        </p>
        <p>
            <strong>Date d'entrée en vigueur :</strong> 14/11/2025
        </p>
    </div>

    <div class="legal-actions">
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Retour au dashboard
        </a>
        <a href="<?= APP_URL ?>/gdpr/export" class="btn btn-secondary">
            <i class="fas fa-download"></i> Exporter mes données
        </a>
    </div>
</div>

<style>
.legal-content {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.legal-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.legal-header h1 {
    font-size: 32px;
    color: var(--dark-color);
    margin-bottom: 8px;
}

.legal-section {
    margin-bottom: 32px;
}

.legal-section h2 {
    font-size: 24px;
    color: var(--primary-color);
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.legal-section:first-of-type h2 {
    border-top: none;
    padding-top: 0;
}

.legal-section h3 {
    font-size: 18px;
    color: var(--dark-color);
    margin-top: 20px;
    margin-bottom: 12px;
    font-weight: 600;
}

.legal-section p,
.legal-section ul {
    line-height: 1.8;
    color: var(--text-color);
}

.legal-section ul {
    margin-left: 20px;
    margin-top: 12px;
}

.legal-section li {
    margin-bottom: 8px;
}

.legal-section a {
    color: var(--primary-color);
    text-decoration: underline;
}

.legal-section a:hover {
    color: var(--primary-hover);
}

/* Tables */
.legal-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.legal-table thead {
    background: var(--primary-color);
    color: white;
}

.legal-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.legal-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.legal-table tbody tr:hover {
    background: #f9f9f9;
}

.legal-table tbody tr:last-child td {
    border-bottom: none;
}

/* Legal Box */
.legal-box {
    background: #f8f9fa;
    border-left: 4px solid var(--primary-color);
    padding: 20px;
    margin: 20px 0;
    border-radius: 4px;
}

.legal-box p {
    margin-bottom: 12px;
}

.legal-box p:last-child {
    margin-bottom: 0;
}

.legal-box strong {
    color: var(--dark-color);
}

/* Utility classes */
.mt-20 {
    margin-top: 20px;
}

.legal-actions {
    margin-top: 40px;
    padding-top: 32px;
    border-top: 2px solid #eee;
    display: flex;
    gap: 16px;
    justify-content: center;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 24px;
    }

    .legal-header h1 {
        font-size: 24px;
    }

    .legal-section h2 {
        font-size: 20px;
    }

    .legal-section h3 {
        font-size: 16px;
    }

    .legal-table {
        font-size: 12px;
    }

    .legal-table th,
    .legal-table td {
        padding: 8px;
    }

    .legal-actions {
        flex-direction: column;
    }

    .legal-box {
        padding: 16px;
    }
}
</style>
