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

Sur PowerShell (Windows) :
```powershell
$env:XDEBUG_MODE="coverage"; ./vendor/bin/infection --threads=4 --min-msi=80
```

Pour lancer le projet, utilisez Docker, en faisant les commandes suivantes:

```
docker compose build
docker compose up
```

Pour implémenter la BDD, il faut appeler les fichiers suivants:

```bash
php SetupDatabase.php # Initialise la base de données
php SeedDatabase.php # Concoie les tables
```

Puis, vous pouvez utiliser le port 8080 et 8081 du localhost pour visualiser la BDD MySQL et le projet
