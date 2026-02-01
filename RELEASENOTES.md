# Hva er nytt i Konrad Office

## Versjon 1.2.0
**Dato:** 1. februar 2026

### Mine aktiviteter

- **Ny personlig side** - Samlet oversikt over dine ventende oppgaver og oppfølgingspunkter
- **Intelligente forslag** - Få intelligente prioriteringsforslag basert på dine aktiviteter, tilbud, fakturaer, arbeidsordrer og prosjekter
- **Arbeidsmengde-score** - Visuell indikator (0-100) som viser hvor mye du har å gjøre
- **Raske gevinster** - Se hvilke oppgaver som kan fullføres raskt
- **Fokusområder** - Identifiser hvor du bør legge mest innsats
- **Mine notater** - Personlige notater med rik tekst-editor som følger deg på tvers av selskaper
- **Fest notater** - Pin viktige notater så de alltid vises øverst

### Forbedret brukeropplevelse

- **Flyout-panel for notater** - Elegant sidepanel for å opprette og redigere notater
- **Ny navigasjon** - "Mine aktiviteter" er nå tilgjengelig direkte fra hovedmenyen

### Tips

Klikk på **Mine aktiviteter** i sidemenyen for å se dine ventende oppgaver. Bruk "Generer forslag"-knappen for å få Intelligente anbefalinger om hva du bør prioritere. Notatene dine er personlige og følger deg selv om du bytter mellom selskaper.

---

## Versjon 1.1.3
**Dato:** 1. februar 2026

### Prosjektleder

- **Ny prosjektleder-felt** - Tilordne en ansatt som prosjektleder for hvert prosjekt
- **Synlig i prosjektlisten** - Se hvem som er ansvarlig direkte i tabelloversikten
- **Enkel tilordning** - Velg prosjektleder fra dropdown-meny når du oppretter eller redigerer et prosjekt

### Prosjekttyper og statuser

- **Ferdigdefinerte prosjekttyper** - Konsulentoppdrag, Utviklingsprosjekt, Supportavtale, Implementering og Opplæring
- **Prosjektstatuser** - Planlegging, Pågår, Fullført, Pause og Kansellert med fargekoder
- **Klart til bruk** - Alle nye selskaper får automatisk tilgang til standard typer og statuser

### Tips

Når du oppretter et nytt prosjekt, velg prosjektleder for å tydeliggjøre hvem som har ansvaret. Prosjektlederen vises i prosjektlisten slik at alle enkelt kan se hvem de skal kontakte.

---

## Versjon 1.1.2
**Dato:** 1. februar 2026

### Nytt dashbord

- **Rollebasert innhold** - Dashbordet viser nå kun informasjon som er relevant for din rolle
- **Mine timer denne uken** - Se hvor mange timer du har ført denne uken og status på timelisten
- **Timer til godkjenning** - Ledere ser ventende timelister som trenger godkjenning
- **Bilag i innboksen** - Økonomibrukere ser antall bilag som venter på behandling
- **Renere design** - Fokusert og oversiktlig layout uten unødvendig informasjon

### Tips

Dashbordet tilpasser seg automatisk basert på din rolle. Økonomibrukere ser økonomirelatert informasjon, mens prosjektledere ser prosjektstatus og arbeidsordrer.

---

## Versjon 1.1.1
**Dato:** 1. februar 2026

### Selskapsanalyse

- **Ny analysemodul** - Få en komplett analyse av selskapets økonomiske helse
- **Styrker og svakheter** - Se hva som fungerer bra og hva som kan forbedres
- **Muligheter og risikoer** - Identifiser vekstmuligheter og potensielle farer
- **Konkrete anbefalinger** - Motta handlingsrettede råd med prioritering
- **Nøkkeltall** - Oversikt over likviditet, lønnsomhet, vekst og kundefordringer

### Tips

Gå til **Økonomi → Analyse** for å kjøre en selskapsanalyse. Analysen gir deg innsikt basert på dine faktiske regnskapsdata og hjelper deg å ta bedre beslutninger.

---

## Versjon 1.1.0
**Dato:** 1. februar 2026

### Timerapporter

- **Ny rapportmodul** - Se oversikt over timer på tvers av ansatte, prosjekter og arbeidsordrer
- **Fleksible filtre** - Filtrer på tidsperiode (denne uken, forrige måned, dette kvartalet, osv.)
- **Fire rapporttyper** - Timer per prosjekt, per arbeidsordre, per ansatt, eller per uke
- **Detaljvisning** - Klikk på et prosjekt for å se hvilke ansatte som har jobbet på det
- **Oppsummering** - Se totaltimer, fakturerbare timer og antall ansatte/prosjekter

### Forbedret validering i timeregistrering

- **Maksgrense per dag** - Kan ikke registrere mer enn 24 timer per dag
- **Validering i service-lag** - Forretningsregler håndheves konsekvent, også for fremtidig API
- **Datovalidering** - Timeføringer må være innenfor riktig uke
- **Krav til mål** - Hver timeføring må ha prosjekt, arbeidsordre eller beskrivelse

### Tips

Gå til **Timer → Rapporter** for å se en oversikt over timer på tvers av hele firmaet. Perfekt for å svare på spørsmål som "Hvor mange timer har vi brukt på Prosjekt X totalt?"

---

## Versjon 1.0.2
**Dato:** 1. februar 2026

### Forbedret teststabilitet

- **Deterministiske factories** - Factory-states for skatteberegninger og MVA-rapporter genererer nå unike verdier for å unngå konflikter
- **Company context** - Forbedret oppsett av firmakontext i tester sikrer korrekt multi-tenancy isolering
- **Policytest-fikser** - InvoicePolicyTest støtter nå komplett firmakontext

### Interne forbedringer

- **802 tester passerer** - Full testdekning på alle kritiske moduler
- **Factories oppdatert** - TaxAdjustmentFactory, VatReportFactory og AnnualAccountNoteFactory med eksplisitte verdier

### Tips

Utviklere som skriver nye tester bør bruke `createTestCompanyContext()` helper-funksjonen fra `tests/Pest.php` for å sette opp firmakontext korrekt.

---

## Versjon 1.0.1
**Dato:** 30. januar 2026

### Sikkerhet og tilgangskontroll

- **Autorisering på alle handlinger** - Alle operasjoner i systemet (opprett, rediger, slett) sjekker nå at brukeren har riktig tilgang
- **Forretningsregler i policies** - Kan ikke slette sendte fakturaer, posterte bilag, eller konverterte tilbud
- **Admin-bypass** - Administratorer har full tilgang til alle funksjoner

### Konfigurerbare kontoklasser

- **Fleksibel kontoplan** - Kontoklasser for rapporter kan nå tilpasses via konfigurasjon
- **Enklere tilpasning** - Støtter ikke-standard kontoplaner uten kodeendringer

### Tips

Administratorer kan nå være trygge på at brukere kun kan endre data de har tilgang til. Systemet håndhever forretningsregler automatisk.

---

## Versjon 1.0.0
**Dato:** 12. januar 2026

### Velkommen til Konrad Office!

Dette er den aller første versjonen av Konrad Office - ditt nye verktøy for enklere bedriftsstyring.

### Hovedfunksjoner

- **Kundehåndtering** - Hold oversikt over alle dine kunder og kontaktpersoner
- **Tilbud og ordrer** - Lag profesjonelle tilbud og konverter dem til ordrer
- **Fakturering** - Send fakturaer og hold oversikt over betalinger
- **Prosjektstyring** - Organiser arbeidet i prosjekter og arbeidsordrer
- **Regnskap** - Bilagsføring, MVA-rapporter og årsregnskap
- **Aksjebok** - Full oversikt over aksjonærer og transaksjoner

### Tips

Du kan alltid finne hjelp ved å klikke på **Hjelp** i sidemenyen. Der finner du guider og svar på vanlige spørsmål.

---

Vi jobber kontinuerlig med å forbedre Konrad Office. Har du tilbakemeldinger eller ønsker? Ta gjerne kontakt!
