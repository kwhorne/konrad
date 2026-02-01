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

Konrad er et komplett forretningssystem designet for norske bedrifter. Systemet tilbyr moduler for kontakthåndtering, vareregister, prosjektstyring, salg, regnskap og MVA-rapportering - alt i en moderne og brukervennlig pakke.

## Navigasjon

Konrad har to hovedpaneler:

### App-panel (`/app`)
Hovedpanelet for daglig drift med følgende struktur:
- **Dashboard** - Oversikt over virksomheten
- **CRM** (ekspanderbar gruppe)
  - Kontakter
  - Varer
  - Tilbud
  - Ordrer
  - Faktura
- **Prosjekt** (ekspanderbar gruppe)
  - Prosjekter
  - Arbeidsordrer
- **Timer** (ekspanderbar gruppe)
  - Timeregistrering
  - Mine timer
  - Godkjenn timer (kun for ledere)
- **Kontrakter** - Kontraktsregister
- **Eiendeler** - Eiendelsregister
- **Økonomi** - Link til økonomi-panelet (kun for økonomi/admin-brukere)
- **Administrasjon** - Brukeradministrasjon (kun for admin-brukere)

### Økonomi-panel (`/economy`)
Dedikert panel for regnskap og økonomi:
- **Dashboard** - Økonomisk oversikt med inntekts-/kostnadsgrafer
- **Økonomi**
  - Bilagsregistrering
  - Innkommende bilag
  - Kundereskontro
  - Leverandørreskontro
  - Rapporter
  - MVA-meldinger
  - Kontoplan
- **Årsoppgjør**
  - Aksjonærregister
  - Skattemelding
  - Årsregnskap
  - Altinn

## Brukerroller

| Rolle | Tilgang |
|-------|---------|
| **Admin** (`is_admin`) | Full tilgang til alle moduler inkl. brukeradministrasjon |
| **Økonomi** (`is_economy`) | Tilgang til `/app` og `/economy` panel |
| **Bruker** | Tilgang kun til `/app` panel |

### Selskapsroller (per selskap)

| Rolle | Tilgang |
|-------|---------|
| **Owner** | Full tilgang, kan godkjenne timer |
| **Manager** | Administrere selskap, kan godkjenne timer |
| **Member** | Standard bruker, kan registrere egne timer |

## Moduler

### Kontaktregister
- Kunder og leverandører med organisasjonsnummer
- Automatisk oppslag i Brønnøysundregistrene
- Adressehåndtering (besøks- og fakturaadresse)
- Kontaktpersoner med roller
- Sosiale medier (LinkedIn, Facebook, Twitter)
- Dokumenter-fane med tilbud, ordrer og fakturaer
- Opprett nye dokumenter direkte fra kontakten
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

### Timeregistrering
- **Ukentlig timeregistrering** for ansatte
- **To metoder for timeføring**:
  - Inline-redigering direkte i ukegrid (skriv timer i cellen)
  - Modal-basert registrering med ekstra felt (dobbelklikk på celle eller bruk "Registrer timer"-knappen)
- **Kobling til prosjekter og arbeidsordrer** (valgfritt)
- **Intern tid** uten prosjekttilknytning (møter, opplæring, etc.)
- **Notatfelt** for å beskrive utført arbeid
- **Godkjenningsflyt**:
  - Utkast → Innsendt → Godkjent/Avvist
  - Ledere (owner/manager) kan godkjenne andres timer
  - Kan ikke godkjenne egne timer
  - Avviste timesedler kan redigeres og sendes på nytt
- **Historikk**: Oversikt over alle egne timesedler med filter på status og år
- **Godkjenningspanel**: Ledere ser ventende timesedler med badge-teller i menyen

### Salg
- **Tilbud**: Opprett og send tilbud til kunder med PDF-generering
- **Ordrer**: Konverter tilbud til ordrer, håndter ordrebekreftelser
- **Fakturaer**: Fakturering med betalingssporing og purringer
- Opprett dokumenter direkte fra kontaktkortet (Dokumenter-fane)
- Linjer med produkter fra vareregisteret
- Auto-nummerering (T-YYYY-NNNN, O-YYYY-NNNN, F-YYYY-NNNN)
- Kreditnotaer
- PDF-generering og e-postutsending

### Økonomi
- **Kontoplan**: Norsk standard NS 4102 med hierarkisk struktur
- **Bilagsregistrering**: Manuell bilagsføring med debet/kredit
- **Kundereskontro**: Oversikt over kundefordringer med aldersfordeling
- **Leverandørreskontro**: Oversikt over leverandørgjeld med aldersfordeling
- **Leverandørfakturaer**: Registrer og betal leverandørfakturaer
- Automatisk bokføring fra fakturaer og betalinger

### Rapporter
- **Hovedbok**: Kontoutdrag for alle konti i en periode
- **Bilagsjournal**: Kronologisk liste over alle bilag
- **Saldobalanse**: Saldo per konto på en gitt dato
- **Resultatregnskap**: Inntekter og kostnader for en periode
- **Balanse**: Eiendeler, gjeld og egenkapital

### MVA-meldinger
- Opprett MVA-meldinger for tomånedlige perioder
- Automatisk beregning fra fakturaer og leverandørfakturaer
- Norske MVA-koder (Alminnelig næring):
  - Salg i Norge: kode 3 (25%), 31 (15%), 33 (12%), 5, 6
  - Kjøp i Norge: kode 1, 11, 13 (fradrag)
  - Import: kode 86, 87, 88
  - Eksport: kode 52
- Manuell overstyring av beløp
- Merknad og vedlegg
- Workflow: Utkast → Beregnet → Sendt → Godkjent/Avvist
- Altinn-referanse ved innsending

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
   cd konrad
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
SALES_ENABLED=true
```

## Ruter

### App-panel
| Rute | Beskrivelse |
|------|-------------|
| `/` | Velkomstside |
| `/app` | Dashboard |
| `/contacts` | Kontaktregister |
| `/products` | Vareregister |
| `/projects` | Prosjekter |
| `/work-orders` | Arbeidsordrer |
| `/timer` | Timeregistrering |
| `/timer/historikk` | Mine timer (historikk) |
| `/timer/godkjenning` | Godkjenn timer (kun ledere) |
| `/quotes` | Tilbud |
| `/orders` | Ordrer |
| `/invoices` | Fakturaer |
| `/contracts` | Kontrakter |
| `/assets` | Eiendeler |
| `/app/settings` | Innstillinger |
| `/admin/*` | Administrasjon (kun admin) |

### Økonomi-panel (krever økonomi- eller admin-rolle)
| Rute | Beskrivelse |
|------|-------------|
| `/economy` | Økonomi dashboard |
| `/economy/accounting` | Bilagsregistrering |
| `/economy/incoming` | Innkommende bilag |
| `/economy/vouchers` | Bilag |
| `/economy/customer-ledger` | Kundereskontro |
| `/economy/supplier-ledger` | Leverandørreskontro |
| `/economy/reports` | Rapporter |
| `/economy/vat-reports` | MVA-meldinger |
| `/economy/accounts` | Kontoplan |
| `/economy/shareholders` | Aksjonærregister |
| `/economy/tax` | Skattemelding |
| `/economy/annual-accounts` | Årsregnskap |
| `/economy/altinn` | Altinn-integrasjon |

## Prosjektstruktur

```
app/
├── Http/
│   ├── Controllers/        # Kontrollere (Contact, Asset, Contract)
│   └── Middleware/
│       ├── SetCurrentCompany.php    # Multi-tenancy context
│       └── EnsureUserBelongsToCompany.php
├── Livewire/               # Livewire-komponenter
│   ├── ProductManager.php
│   ├── ProjectManager.php
│   ├── WorkOrderManager.php
│   ├── TimesheetManager.php        # Timeregistrering
│   ├── TimesheetHistory.php        # Mine timer
│   ├── TimesheetApprovalManager.php # Godkjenning av timer
│   ├── QuoteManager.php
│   ├── OrderManager.php
│   ├── InvoiceManager.php
│   ├── VoucherManager.php
│   ├── VatReportManager.php
│   ├── CompanyProfileManager.php   # Selskapsinnstillinger
│   ├── CompanyUserManager.php      # Brukeradministrasjon
│   └── UserManager.php             # Admin brukeradmin
├── Models/
│   ├── Company.php         # Multi-tenant selskap
│   ├── Contact.php
│   ├── Product.php
│   ├── Project.php
│   ├── WorkOrder.php
│   ├── Timesheet.php       # Ukentlig timeseddel
│   ├── TimesheetEntry.php  # Daglig timeføring
│   ├── Quote.php
│   ├── Order.php
│   ├── Invoice.php
│   ├── Account.php
│   ├── Voucher.php
│   └── Traits/
│       └── BelongsToCompany.php    # Multi-tenant scope
├── Jobs/
│   └── Concerns/
│       └── HasCompanyContext.php   # Job company context
└── Services/               # Business logic
    ├── CompanyService.php  # Selskaps- og brukeradmin
    ├── ContactService.php  # Kontakthåndtering
    ├── InvoiceService.php
    ├── ProjectService.php
    ├── WorkOrderService.php
    ├── TimesheetService.php # Timeregistrering
    ├── VoucherService.php
    └── VatReportService.php

database/
├── migrations/             # Database-migrasjoner
└── seeders/               # Seeders for testdata
    ├── AccountSeeder.php   # NS 4102 kontoplan
    └── VatCodeSeeder.php   # MVA-koder

resources/views/
├── components/            # Blade-komponenter
├── livewire/             # Livewire-views
├── accounting/           # Økonomi-views
├── reports/              # Rapport-views
├── vat-reports/          # MVA-melding views
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

| E-post | Passord | Roller |
|--------|---------|--------|
| admin@example.com | password | Administrator (`is_admin`) |
| economy@example.com | password | Økonomi (`is_economy`) |
| user@example.com | password | Vanlig bruker |

## Lisens

Dette prosjektet er lisensiert under [MIT-lisensen](https://opensource.org/licenses/MIT).
