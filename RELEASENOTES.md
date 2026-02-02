# Hva er nytt i Konrad Office

## Versjon 1.4.4
**Dato:** 2. februar 2026

### Prosjektvedlegg

- **Filopplasting** - Last opp dokumenter direkte til prosjekter med drag-and-drop
- **Flere filer** - Støtte for å laste opp flere filer samtidig
- **Filtyper** - Støtter PDF, Word, Excel, bilder og andre dokumentformater
- **Forhåndsvisning** - Bilder vises med miniatyrbilder, andre filer med filtype-ikon
- **Vedleggsindikator** - Prosjektlisten viser binders-ikon for prosjekter med vedlegg
- **Last ned** - Klikk for å laste ned vedlegg direkte fra prosjektvisningen
- **Sletting** - Fjern vedlegg med bekreftelsesdialog

### Tips

Åpne et prosjekt via **Prosjekter**-menyen og rull ned til seksjonen **Vedlegg**. Dra og slipp filer i dropzonen eller klikk for å velge filer fra datamaskinen. Filer vises med navn og størrelse før du klikker **Last opp**. Eksisterende vedlegg vises under med mulighet for nedlasting og sletting.

---

## Versjon 1.4.3
**Dato:** 2. februar 2026

### Bankavstemming

- **CSV-import fra bank** - Last opp kontoutskrifter fra DNB, Nordea, SpareBank 1 og Sbanken
- **Auto-deteksjon av format** - Systemet gjenkjenner bankformat automatisk basert på CSV-struktur
- **Automatisk matching** - Transaksjoner matches mot fakturabetalinger og leverandørfakturaer
- **KID-matching** - Norske KID-referanser brukes for presis matching av innbetalinger
- **Manuell matching** - Søk og match umatchede transaksjoner mot åpne poster
- **Kladd-bilag** - Opprett nye bilag direkte fra banktransaksjoner
- **Full sporbarhet** - Se hvilke transaksjoner som er matchet mot hvilke bilag

### Forbedret tegnstøtte

- **Norske tegn i CSV** - Forbedret håndtering av æ, ø og å i importerte filer
- **Automatisk encoding-deteksjon** - Støtter UTF-8, ISO-8859-1 og Windows-1252
- **BOM-håndtering** - Fjerner automatisk BOM fra filer

### Tips

Gå til **Økonomi → Bankavstemming** for å importere en kontoutskrift. Velg bankkonto og last opp CSV-filen. Systemet forsøker automatisk å matche transaksjoner mot fakturaer og leverandørfakturaer. For umatchede transaksjoner kan du enten søke manuelt etter en match, eller opprette et kladd-bilag som bokføres automatisk.

---

## Versjon 1.4.2
**Dato:** 2. februar 2026

### Multi-tenancy: Flere selskaper per bruker

- **Selskapsoppretting** - Nye brukere går gjennom en onboarding-wizard for å opprette sitt første selskap
- **Brønnøysund-integrasjon** - Søk på organisasjonsnummer og hent firmainformasjon automatisk
- **Selskapsveksler** - Brukere som tilhører flere selskaper kan enkelt bytte mellom dem via profilmenyen
- **Roller per selskap** - Eier, leder eller medlem med ulike rettigheter per selskap
- **Brukeradministrasjon** - Eiere og ledere kan invitere brukere til selskapet via e-post
- **Full dataisolering** - All forretningsdata er isolert per selskap med automatisk filtrering

### Selskapsinnstillinger

- **Selskapsprofil** - Rediger selskapsinformasjon, logo og bankopplysninger
- **Dokumentmaler** - Tilpass faktura- og tilbudsvilkår, dokumentfooter
- **Standardverdier** - Sett betalingsfrist og tilbudsgyldighet for selskapet

### Forbedret brukerdokumentasjon

- **Kompakt header** - Hjelpesiden har nå en mer kompakt header med mindre luft
- **Norske tegn** - All dokumentasjon bruker nå korrekte norske tegn (æøå)

### Tips

Klikk på **Innstillinger** i sidemenyen for å administrere selskapet ditt. Under fanen **Selskap** kan du redigere selskapsinformasjon og dokumentmaler. Under fanen **Brukere** kan eiere og ledere invitere nye brukere og administrere roller. Hvis du tilhører flere selskaper, kan du bytte mellom dem via profilmenyen øverst til høyre.

---

## Versjon 1.4.1
**Dato:** 1. februar 2026

### Tofaktorautentisering (2FA)

- **TOTP-basert 2FA** - Bruk Google Authenticator, Authy eller lignende apper for ekstra sikkerhet
- **QR-kode oppsett** - Enkel aktivering via QR-kode i innstillingene
- **Gjenopprettingskoder** - Engangskoder for tilgang hvis du mister autentiseringsappen
- **5-dagers karensperiode** - Nye brukere får 5 dager til å aktivere 2FA før kontoen låses
- **Påminnelsesbanner** - Vennlig påminnelse på alle sider med nedtelling

### Admin IP-whitelist

- **2FA-unntak for kontor** - Administratorer kan whiteliste IP-adresser som slipper 2FA-verifisering
- **CIDR-støtte** - Støtter både enkelt-IP og IP-områder (f.eks. 192.168.1.0/24)
- **Aktiver/deaktiver** - Slå IP-adresser av og på uten å slette dem

### Tips

Gå til **Innstillinger → Min konto → Sikkerhet** for å aktivere tofaktorautentisering. Skann QR-koden med autentiseringsappen din og bekreft med en kode. Husk å lagre gjenopprettingskodene et trygt sted - du trenger dem hvis du mister tilgang til autentiseringsappen.

---

## Versjon 1.4.0
**Dato:** 1. februar 2026

### Avdelinger som konteringsdimensjon

- **Avdelingsregister** - Opprett og administrer avdelinger for selskapet med kode og navn
- **Bruker-avdeling** - Tildel brukere til avdelinger via brukeradministrasjonen
- **Dimensjon på bilag** - Avdeling følger med på alle bilagslinjer for detaljert rapportering
- **Auto-propagering** - Avdeling arves automatisk fra bruker → dokument → bilag
- **Valgfri aktivering** - Slå av/på avdelinger per selskap i regnskapsinnstillinger
- **Rapportfilter** - Filtrer hovedbok og andre rapporter på avdeling

### Kontoplan

- **Kontoadministrasjon** - Full CRUD for kontoer med nummer, navn, klasse og type
- **NS 4102 ett-klikk** - Opprett norsk standard kontoplan med over 200 kontoer på ett klikk
- **MVA-koder** - Koble kontoer til MVA-koder for automatisk MVA-beregning
- **Systemkontoer** - Beskytt viktige systemkontoer mot utilsiktet sletting

### Regnskapsinnstillinger

- **Ny innstillingsside** - Konfigurerbare regnskapsinnstillinger per selskap
- **Avdelinger på/av** - Aktiver avdelingsdimensjon når du er klar
- **Krav om avdeling** - Valgfritt krav om avdeling på alle bilag
- **Standardavdeling** - Sett en standard avdeling for nye bilag

### Tips

Gå til **Innstillinger → Avdelinger** for å opprette avdelinger. Aktiver avdelingsfunksjonen under **Innstillinger → Regnskap**. Når avdelinger er aktivert kan du tildele brukere til avdelinger under **Innstillinger → Brukere**. Avdelingen følger automatisk med på alle dokumenter og bilag brukeren oppretter, slik at du får full sporbarhet i hovedboken.

---

## Versjon 1.3.2
**Dato:** 1. februar 2026

### Varetelling

- **Tellesjon** - Opprett tellinger for hele eller deler av lageret
- **Registrering** - Registrer talt antall for hvert produkt med avviksforklaring
- **Avviksrapport** - Se forventet vs talt mengde med automatisk beregning av verdidifferanse
- **Bokforing** - Automatisk opprettelse av lagerjusteringer ved avvik
- **Dokumentasjon** - Full historikk over alle tellinger for revisjon

### Lageroversikt med live data

- **Nokkeltall** - Dashbordet viser na antall lagervarer, total lagerverdi, apne bestillinger og varer under bestillingspunkt
- **Siste bevegelser** - Se de siste lagertransaksjonene direkte pa dashbordet
- **Siste varemottak** - Rask tilgang til nylige varemottak med status og lenker
- **Vektet verdi** - Total lagerverdi beregnes basert pa vektet gjennomsnittskost

### Tips

Ga til **Lager → Varetelling** for a opprette en ny telling. Velg lokasjon og start tellingen. Du kan raskt godkjenne produkter som stemmer med ett klikk, eller registrere avvikende antall med forklaring. Bokfor tellingen nar du er ferdig for a opprette lagerjusteringer automatisk. Husk at arlig varetelling er et lovkrav ved regnskapsarets slutt.

---

## Versjon 1.3.0
**Dato:** 1. februar 2026

### Lager og innkjøp

- **Lagerhold** - Full lagerstyring med transaksjonsbasert sporing av alle varebevegelser
- **Innkjopsordrer** - Opprett innkjopsordrer til leverandorer med godkjenningsflyt
- **Varemottak** - Registrer mottak av varer basert pa innkjopsordre eller fritstaende
- **Lagerlokasjoner** - Stotte for flere lagre med hierarkisk struktur (lager, sone, hylle)
- **Automatisk reservering** - Varer reserveres automatisk nar ordrer bekreftes
- **Lageruttak ved fakturering** - Automatisk trekk fra lager nar ordre konverteres til faktura
- **Vektet gjennomsnittskost** - Automatisk beregning av varekostnad ved salg

### Lageroversikt

- **Beholdning** - Se lagerbeholdning per produkt og lokasjon
- **Transaksjonshistorikk** - Full sporbarhet pa alle lagerbevegelser
- **Lagerjusteringer** - Manuell justering ved opptelling eller svinn
- **Bestillingspunkt** - Varsling nar varer nar bestillingspunkt

### Tips

Aktiver lagermodulen ved a sette `INVENTORY_ENABLED=true` i .env-filen. Ga til **Lager** i sidemenyen for a opprette lagerlokasjoner og begynne a fore lagerbeholdning. Merk produkter som "lagerfort" for a aktivere lagertransaksjoner pa dem.

---

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
