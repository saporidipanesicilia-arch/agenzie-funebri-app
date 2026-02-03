# 🚀 Setup Rapido - Guida Passo-Passo

## Prerequisiti
- ✅ Laravel installato
- ✅ Laragon attivo
- ⏳ PostgreSQL da configurare

---

## Step 1: Configurare PostgreSQL

### Opzione A: Usa PostgreSQL di Laragon (Raccomandato)

1. Apri Laragon
2. Click destro → **PostgreSQL** → **Start**
3. Le credenziali di default sono:
   ```
   Host: localhost
   Port: 5432
   Username: postgres
   Password: (vuota o "root")
   ```

### Opzione B: Installa PostgreSQL standalone

1. Scarica da: https://www.postgresql.org/download/windows/
2. Installa con wizard
3. Annota username e password

---

## Step 2: Creare il Database

### Con pgAdmin (GUI)
1. Apri pgAdmin (incluso in PostgreSQL o Laragon)
2. Right-click su "Databases" → "Create" → "Database"
3. Nome: `agenzie_funebri`
4. Salva

### Con terminale
```bash
psql -U postgres
CREATE DATABASE agenzie_funebri;
\q
```

---

## Step 3: Configurare `.env`

Apri il file `.env` nella root del progetto e aggiorna:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agenzie_funebri
DB_USERNAME=postgres
DB_PASSWORD=          # Lascia vuoto o inserisci la tua password
```

---

## Step 4: Eseguire Setup (Terminale Laragon)

Naviga alla directory del progetto:
```powershell
cd "c:\Users\tigno\Desktop\APP\agenzie funebri"
```

### 1. Rigenera autoload
```powershell
composer dump-autoload
```

### 2. Esegui migrations
```powershell
php artisan migrate
```

### 3. Popola database con dati di test
```powershell
php artisan db:seed
```

Vedrai l'output con le credenziali di login.

---

## Step 5: Installare Autenticazione (Laravel Breeze)

```powershell
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

---

## Step 6: Avviare il Server

```powershell
php artisan serve
```

Apri il browser: **http://localhost:8000**

---

## 🔑 Credenziali di Login (Dopo Seed)

### Agenzia Piccola: Onoranze Funebri Rossi
- **Owner**: `mario.rossi@onoranzefunebrirossi.it`
- **Operator**: `giulia.bianchi@onoranzefunebrirossi.it`

### Agenzia Grande: Gruppo Funerario Lombardo
- **Owner**: `carlo.verdi@gruppofunerariolombardo.it`
- **Branch Manager Milano**: `laura.neri@gruppofunerariolombardo.it`
- **Branch Manager Monza**: `marco.ferrari@gruppofunerariolombardo.it`
- **Operator Bergamo**: `anna.conti@gruppofunerariolombardo.it`
- **Staff (tutte le sedi)**: `paolo.colombo@gruppofunerariolombardo.it`

**Password per tutti**: `password`

---

## ⚠️ Troubleshooting

### Errore "SQLSTATE[08006]"
→ PostgreSQL non è attivo. Avvia da Laragon.

### Errore "database does not exist"
→ Crea il database `agenzie_funebri` (vedi Step 2).

### Errore "Class not found"
→ Esegui `composer dump-autoload`.

### Breeze: "npm command not found"
→ Installa Node.js da Laragon o da nodejs.org.

---

## 🎯 Verificare che Tutto Funziona

1. Vai a `http://localhost:8000/login`
2. Login con una delle credenziali sopra
3. Dovresti vedere la dashboard
4. Verifica che il tenant isolation funziona:
   - Mario Rossi (Agenzia 1) non deve vedere i dati di Agenzia 2
   - Laura Neri (Milano) non deve vedere i dati di altre sedi

---

## Prossimi Step

Una volta completato il setup:
1. ✅ Testare autenticazione
2. ✅ Verificare tenant isolation
3. 🔜 Creare il modulo Funerals
4. 🔜 Creare Timeline Wizard
5. 🔜 Implementare Documents & Pratiche

**Tutto pronto! Ora puoi iniziare a sviluppare i moduli di dominio.** 🚀
