# Konrad - Forretningssystem

<p align="center">
    <strong>Konrad</strong> - Et moderne forretningssystem bygget med Laravel og Flux UI
</p>

<p align="center">
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-v12-FF2D20?style=flat&logo=laravel" alt="Laravel v12"></a>
    <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-v3-FB70A9?style=flat" alt="Livewire v3"></a>
    <a href="https://fluxui.dev"><img src="https://img.shields.io/badge/Flux%20UI-v2-4F46E5?style=flat" alt="Flux UI v2"></a>
    <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind%20CSS-v4-38B2AC?style=flat&logo=tailwind-css" alt="Tailwind CSS v4"></a>
</p>

## Om Konrad

Konrad er et komplett forretningssystem designet for norske bedrifter. Systemet tilbyr moduler for kontakthåndtering, vareregister, prosjektstyring og arbeidsordrer - alt i en moderne og brukervennlig pakke.

## Moduler

### Kontaktregister
- Kunder og leverandører med organisasjonsnummer
- Adressehåndtering og kontaktinformasjon
- Aktivitetslogg med tilpassbare aktivitetstyper
- Kobling til prosjekter og arbeidsordrer

### Vareregister
- Produkter og tjenester med SKU
- Varegrupper og varetyper
- MVA-satser og enheter
- Prissetting med kostpris

### Prosjekter
- Prosjektstyring med budsjett og timer
- Kobling til kontakter
- Prosjektstatuser og typer
- Prosjektlinjer for produkter

### Arbeidsordrer
- Komplett arbeidsordresystem
- Auto-genererte ordrenummer (WO-YYYY-NNNN)
- 8 statuser: Ny, Planlagt, Pågår, Venter, Fullført, Godkjent, Fakturert, Kansellert
- 4 prioritetsnivåer: Lav, Normal, Høy, Kritisk
- 5 typer: Service, Reparasjon, Installasjon, Vedlikehold, Konsultasjon
- Timeregistrering med utført av og dato
- Produktlinjer fra vareregisteret
- Kobling til kontakter og prosjekter
- Tildeling til ansvarlig bruker

## Teknisk stack

- **Backend**: Laravel 12, PHP 8.3
- **Frontend**: Livewire 3, Flux UI Pro v2, Tailwind CSS v4
- **Database**: MySQL
- **Testing**: Pest

## Installasjon

### Forutsetninger

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL
- [Laravel Herd](https://herd.laravel.com) (anbefalt)

### Oppsett

1. **Klon repositoriet**
   ```bash
   git clone <repository-url>
   cd konrad2
   ```

2. **Installer avhengigheter**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Miljøkonfigurasjon**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start utvikling**
   ```bash
   npm run dev
   ```

## Konfigurasjon

### Feature Toggles

Moduler kan aktiveres/deaktiveres via `.env`:

```env
CONTRACTS_ENABLED=true
ASSETS_ENABLED=true
CONTACTS_ENABLED=true
PRODUCTS_ENABLED=true
PROJECTS_ENABLED=true
WORK_ORDERS_ENABLED=true
```

## Ruter

| Rute | Beskrivelse |
|------|-------------|
| `/` | Velkomstside |
| `/app` | Dashboard |
| `/contacts` | Kontaktregister |
| `/products` | Vareregister |
| `/projects` | Prosjekter |
| `/work-orders` | Arbeidsordrer |
| `/app/settings` | Innstillinger |
| `/admin/*` | Administrasjon (kun admin) |

## Prosjektstruktur

```
app/
├── Http/Controllers/        # Kontrollere
├── Livewire/               # Livewire-komponenter
│   ├── ContactManager.php
│   ├── ProductManager.php
│   ├── ProjectManager.php
│   └── WorkOrderManager.php
└── Models/                 # Eloquent-modeller
    ├── Contact.php
    ├── Product.php
    ├── Project.php
    ├── WorkOrder.php
    └── ...

database/
├── migrations/             # Database-migrasjoner
└── seeders/               # Seeders for testdata

resources/views/
├── components/            # Blade-komponenter
├── livewire/             # Livewire-views
└── ...
```

## Utvikling

### Kodeformatering
```bash
vendor/bin/pint
```

### Testing
```bash
php artisan test
```

### Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## Testbrukere

Etter seeding er følgende brukere tilgjengelige:

| E-post | Passord | Rolle |
|--------|---------|-------|
| admin@example.com | password | Administrator |
| user@example.com | password | Bruker |

## Lisens

Dette prosjektet er lisensiert under [MIT-lisensen](https://opensource.org/licenses/MIT).
