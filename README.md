## Exécution rapide

Deux commandes simples (définies comme scripts composer) :

```bash
composer coverage   # runs tests with coverage
composer mutation   # runs mutation testing (Infection)
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
