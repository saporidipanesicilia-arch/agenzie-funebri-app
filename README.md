# 🏛️ Sistema Gestionale Agenzie Funebri

**Micro-SaaS verticale per agenzie funebri** - Multi-tenant, Multi-sede, Production-ready

---

## 🎯 Caratteristiche Principali

- ✅ **Multi-tenant**: Un database, dati isolati per agenzia
- ✅ **Multi-sede**: Supporto nativo per agenzie con più sedi
- ✅ **Role-Based Access**: Owner, Branch Manager, Operator, Staff
- ✅ **UUID per sicurezza**: Nessun ID interno esposto
- ✅ **Soft Deletes**: Recupero dati accidentalmente cancellati
- ✅ **Tenant Isolation**: Filtraggio automatico query

---

## 🏗️ Stack Tecnologico

- **Backend**: Laravel 12
- **Database**: PostgreSQL
- **Frontend**: Blade + Vanilla JS (no SPA)
- **Auth**: Laravel Breeze
- **Env**: Laragon (Windows)

---

## 📚 Documentazione

- **[Setup Guide](.agent/docs/setup-guide.md)** - Guida completa per installazione
- **[Architecture](.agent/docs/architecture.md)** - Design e architettura del sistema
- **[Foundation Implementation](.agent/docs/foundation-implementation.md)** - Dettagli implementazione
- **[Verify Setup](.agent/docs/verify-setup.md)** - Script di verifica

---

## 🚀 Quick Start

### 1. Configura PostgreSQL

Apri Laragon → Avvia PostgreSQL → Crea database `agenzie_funebri`

### 2. Configura `.env`

```env
DB_CONNECTION=pgsql
DB_DATABASE=agenzie_funebri
DB_USERNAME=postgres
DB_PASSWORD=
```

### 3. Setup

Nel terminale Laragon:

```bash
composer dump-autoload
php artisan migrate
php artisan db:seed
```

### 4. Installa Auth (opzionale)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
```

### 5. Avvia Server

```bash
php artisan serve
```

Vai a: **http://localhost:8000**

---

## 🔑 Credenziali Demo

Dopo `php artisan db:seed`:

### Agenzia 1 (Piccola)
- **mario.rossi@onoranzefunebrirossi.it** (owner)
- **giulia.bianchi@onoranzefunebrirossi.it** (operator)

### Agenzia 2 (Grande - 3 sedi)
- **carlo.verdi@gruppofunerariolombardo.it** (owner)
- **laura.neri@gruppofunerariolombardo.it** (branch_manager - Milano)
- **marco.ferrari@gruppofunerariolombardo.it** (branch_manager - Monza)
- **anna.conti@gruppofunerariolombardo.it** (operator - Bergamo)

**Password**: `password` (per tutti)

---

## 🏛️ Architettura

### Domain Modules (Planned)
```
app/Domain/
├── Core/          # Agencies, Branches, Users, Roles
├── Funerals/      # Gestione funerali
├── Documents/     # Pratiche e documenti
├── Logistics/     # Mezzi, squadre, assegnazioni
├── Finance/       # Preventivi, marginalità
├── Cemeteries/    # Cimiteri, mappe, registro defunti
└── FamilyCloud/   # Accesso famiglie (QR code)
```

### Infrastructure
```
app/Infrastructure/
├── Traits/
│   └── BelongsToTenant.php      # Auto tenant scoping
├── Scopes/
│   └── TenantScope.php          # Global query filter
└── Middleware/
    └── EnsureTenantIsSet.php    # Tenant validation
```

---

## 👥 Ruoli e Permessi

| Ruolo | Descrizione | Accesso |
|-------|-------------|---------|
| **admin** | Super-admin | Cross-tenant |
| **owner** | Titolare agenzia | Tutte le sedi |
| **branch_manager** | Responsabile sede | Sede assegnata |
| **operator** | Operatore | Sede assegnata |
| **staff** | Personale | View-only + task assegnati |

---

## 🔒 Sicurezza

- **Tenant Isolation**: Tutte le query filtrate automaticamente per `agency_id`
- **UUID Routes**: `/funerals/{uuid}` invece di `/funerals/123`
- **Email Unique per Tenant**: Stesso email può esistere in agenzie diverse
- **Validation Middleware**: Verifica agency attiva, user attivo
- **Soft Deletes**: Dati mai persi completamente
- **Audit Logs**: (Planned) Tracciamento modifiche

---

## 📋 Moduli Pianificati

### ✅ Done
- [x] Core (Agencies, Branches, Users)
- [x] Multi-tenant foundation
- [x] Authentication setup

### 🔜 Next
- [ ] Timeline Wizard (step configurabili per funerale)
- [ ] Funerals Module (anagrafica defunto, familiari)
- [ ] Documents Module (upload, semaforo stato)
- [ ] Logistics Module (mezzi, squadre, assegnazioni)
- [ ] Tavolo della Memoria (catalogo cofani, preventivi)
- [ ] Family Cloud (accesso QR per famiglie)
- [ ] Preventivi & Marginalità
- [ ] Cimiteri & Mappe (PDF/JPG, registro defunti)

---

## 🛠️ Comandi Utili

```bash
# Migrations
php artisan migrate                 # Esegui migrations
php artisan migrate:fresh --seed    # Reset + seed
php artisan migrate:status          # Status migrations

# Seeders
php artisan db:seed                 # Popola DB
php artisan db:seed --class=AgencySeeder

# Tinker (interattivo)
php artisan tinker
>>> Agency::count()
>>> User::where('role', 'owner')->get()

# Routes
php artisan route:list              # Lista tutte le routes

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 📝 Convenzioni Codice

- **Nomi variabili**: Termini dominio funebre (deceased, funeral, grave)
- **Comments**: Sempre in punti critici
- **UUID**: Mai esporre ID interni
- **Soft Deletes**: Su tutte le entità principali
- **Tenant Scoping**: Automatico via trait `BelongsToTenant`

---

## 🤝 Contribuire

### Regole
1. Codice production-ready (no placeholder, no TODO)
2. Rispettare architettura multi-tenant
3. Test per nuove features
4. Commenti per logica non ovvia

---

**Fatto con ❤️ per agenzie funebri**
