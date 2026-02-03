# Verifica Configurazione Sistema

Esegui questo script per verificare che tutto sia configurato correttamente.

## Da eseguire nel terminale Laragon

```powershell
# 1. Verifica Composer
Write-Host "`n=== Composer ===" -ForegroundColor Cyan
composer --version

# 2. Verifica PHP
Write-Host "`n=== PHP ===" -ForegroundColor Cyan
php --version

# 3. Verifica Laravel
Write-Host "`n=== Laravel ===" -ForegroundColor Cyan
php artisan --version

# 4. Verifica Node.js (opzionale)
Write-Host "`n=== Node.js ===" -ForegroundColor Cyan
node --version
npm --version

# 5. Verifica connessione PostgreSQL
Write-Host "`n=== Database Connection ===" -ForegroundColor Cyan
php artisan migrate:status

# 6. Lista migrations disponibili
Write-Host "`n=== Migrations ===" -ForegroundColor Cyan
Get-ChildItem database\migrations -Name

# 7. Lista models
Write-Host "`n=== Models ===" -ForegroundColor Cyan
Get-ChildItem app\Models -Name

# 8. Verifica middleware registrati
Write-Host "`n=== Middleware ===" -ForegroundColor Cyan
php artisan route:list --columns=method,uri,name,middleware

Write-Host "`n✅ Verifica completata!" -ForegroundColor Green
```

## Risultato Atteso

Se tutto è configurato correttamente dovresti vedere:
- ✅ Composer 2.x
- ✅ PHP 8.2+
- ✅ Laravel 12.x
- ✅ Node.js 18+ (se installato)
- ✅ Database connesso
- ✅ 3 migrations create
- ✅ 3 models (Agency, Branch, User)
- ✅ Middleware 'tenant' registrato
