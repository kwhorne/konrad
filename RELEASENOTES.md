# Hva er nytt i Konrad Office

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
