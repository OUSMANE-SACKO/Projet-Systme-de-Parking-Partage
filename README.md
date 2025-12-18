## Exécution rapide

Deux commandes simples (définies comme scripts composer) :

```bash
composer coverage   # Run les tests de couverture
composer open-coverage # Run le résultat sur un fichier html
```

```bash
composer mutation   # runs mutation testing (Infection)
composer open-mutation # runs the html results
```

Exemples d'implémentation pour ces scripts (si besoin) :

```bash
# composer coverage -> lance PHPUnit avec couverture
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text

# composer mutation -> lance Infection en utilisant la couverture
XDEBUG_MODE=coverage ./vendor/bin/infection --threads=4 --min-msi=80
```

Sur PowerShell (Windows) :
```powershell
$env:XDEBUG_MODE="coverage"; ./vendor/bin/infection --threads=4 --min-msi=80
```

Pour implémenter la BDD, il faut appeler les fichiers suivants:

```bash
php SetupDatabase.php # Initialise la base de données
php SeedDatabase.php # Concoie les tables
```