# Fonctionnalité de Réinitialisation de Mot de Passe

## Vue d'ensemble

Cette fonctionnalité permet aux utilisateurs de réinitialiser leur mot de passe en cas d'oubli. Le processus utilise un système de jeton sécurisé avec expiration.

## Fichiers ajoutés/modifiés

### Nouveaux fichiers créés :
1. **`src/public/forgot_password.php`** - Page de demande de réinitialisation
2. **`src/public/reset_password.php`** - Page de saisie du nouveau mot de passe
3. **`src/db/migration_password_reset.sql`** - Script de migration de la base de données

### Fichiers modifiés :
1. **`src/public/index.php`** - Ajout du lien "Mot de passe oublié ?"
2. **`src/includes/functions.php`** - Ajout de la fonction `sendPasswordResetEmail()`
3. **`src/db/init.sql`** - Ajout des colonnes `reset_token` et `reset_token_expiry`

## Structure de la base de données

Deux nouvelles colonnes ont été ajoutées à la table `users` :

```sql
reset_token VARCHAR(64) NULL
reset_token_expiry TIMESTAMP NULL
```

## Flux de travail

### 1. Demande de réinitialisation
- L'utilisateur clique sur "Mot de passe oublié ?" sur la page de connexion
- Il est redirigé vers `forgot_password.php`
- Il entre son adresse email
- Le système génère un jeton unique et l'enregistre avec une date d'expiration (1 heure)
- Un email est envoyé avec un lien de réinitialisation

### 2. Réinitialisation du mot de passe
- L'utilisateur clique sur le lien dans l'email
- Il est redirigé vers `reset_password.php?token=...`
- Le système vérifie que le jeton est valide et non expiré
- L'utilisateur saisit son nouveau mot de passe
- Le mot de passe est mis à jour et les jetons sont effacés

## Sécurité

### Mesures de sécurité implémentées :
1. **Jeton aléatoire sécurisé** - Généré avec `bin2hex(random_bytes(32))` (64 caractères hexadécimaux)
2. **Expiration du jeton** - Valide uniquement pendant 1 heure
3. **Hachage du mot de passe** - Utilisation de `password_hash()` avec PASSWORD_DEFAULT
4. **Validation des entrées** - Vérification de la longueur minimale (8 caractères)
5. **Message générique** - Même message de succès que l'email existe ou non (pour éviter l'énumération)
6. **Effacement du jeton** - Le jeton est supprimé après utilisation

## Configuration email

La fonctionnalité utilise le même système d'envoi d'email que la vérification de compte.
Assurez-vous que les variables d'environnement SMTP sont configurées dans votre `docker-compose.yml` :

```yaml
SMTP_HOST: ${SMTP_HOST}
SMTP_PORT: ${SMTP_PORT}
SMTP_USERNAME: ${SMTP_USERNAME}
SMTP_PASSWORD: ${SMTP_PASSWORD}
SMTP_FROM: ${SMTP_FROM}
```

## Installation

### Pour une nouvelle installation :
Les colonnes sont automatiquement créées lors de l'initialisation de la base de données via `init.sql`.

### Pour une installation existante :
Exécutez le script de migration :

```bash
docker-compose exec -T db mysql -u root -p[VOTRE_MOT_DE_PASSE_ROOT] camagru < src/db/migration_password_reset.sql
```

## Test de la fonctionnalité

1. Accédez à `https://localhost:8443/`
2. Cliquez sur "Forgot password?"
3. Entrez une adresse email valide d'un utilisateur existant
4. Vérifiez l'email reçu
5. Cliquez sur le lien de réinitialisation
6. Entrez un nouveau mot de passe (minimum 8 caractères)
7. Connectez-vous avec votre nouveau mot de passe

## Messages utilisateur

### Messages de succès :
- "A password reset link has been sent to your email address."
- "Your password has been successfully reset. You can now log in with your new password."

### Messages d'erreur :
- "Email is required"
- "Invalid email format"
- "No reset token provided."
- "Invalid or expired reset link."
- "Password is required"
- "Password must contain at least 8 characters"
- "Passwords do not match"

## Notes supplémentaires

- Le lien de réinitialisation expire après 1 heure
- Un seul jeton de réinitialisation peut être actif à la fois par utilisateur
- Le jeton est automatiquement supprimé après utilisation
- La fonctionnalité est compatible avec le système de classe `Elem` existant pour la génération HTML
