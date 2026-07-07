# Kiekje — projectdocumentatie

## 1. Backlog & MoSCoW

| # | Userstory | MoSCoW | Storypoints |
|---|-----------|--------|-------------|
| 1 | Als gebruiker wil ik een account aanmaken zodat ik kan inloggen. | Must | 3 |
| 2 | Als gebruiker wil ik inloggen zodat mijn sessie herkend wordt. | Must | 2 |
| 3 | Als gebruiker wil ik een foto met bijschrift plaatsen. | Must | 5 |
| 4 | Als gebruiker wil ik andermans kiekjes kunnen liken. | Must | 2 |
| 5 | Als gebruiker wil ik kunnen reageren op een kiekje. | Should | 3 |
| 6 | Als gebruiker wil ik verhalen (stories) kunnen bekijken/plaatsen. | Should | 5 |
| 7 | Als gebruiker wil ik andere gebruikers kunnen volgen. | Should | 3 |
| 8 | Als gebruiker wil ik mijn profielgrid kunnen bekijken. | Must | 2 |
| 9 | Als gebruiker wil ik pushmeldingen ontvangen bij een like. | Could | 3 |
| 10 | Als gebruiker wil ik berichten kunnen sturen (DM). | Won't (deze sprint) | 8 |

Storypoints zijn geschat met Planning Poker (Fibonacci-reeks) in het team.

## 2. Werken in sprints & scrum-board

- Sprintduur: 2 weken.
- Scrum-board (kolommen): *Backlog → To do → In progress → Review → Done*.
- Elke sprint start met sprintplanning (backlog-items uit Must/Should) en eindigt
  met een **retrospective**.

### Retrospective-template (elke sprint)
- Wat ging goed?
- Wat kan beter?
- Concrete actiepunten voor volgende sprint.

## 3. Gesprektechnieken & voortgangsgesprek met teamlead

- **Op tijd aan de bel trekken**: blokkades direct melden in de dagstart of
  direct aan de teamlead, niet pas aan het einde van de sprint.
- Bij het gesprek met de teamlead: STARR-methode gebruiken (Situatie, Taak,
  Actie, Resultaat, Reflectie) om voortgang en obstakels toe te lichten.
- **Reflecteren**: na elke sprint kort reflecteren op eigen aandeel, wat je
  hebt geleerd (technisch en samenwerking) en wat je anders zou doen.

## 4. Versiebeheer

- Git-flow: `main` (stabiel/live), `develop` (integratie), `feature/<naam>`
  per taak. Pull requests + code review vóór mergen naar `develop`.
- Commit-conventie: `feat:`, `fix:`, `docs:`, `test:` prefixes.
- **Scheiding test- en live-omgeving**: aparte `.env`-bestanden en aparte
  databases (`kiekje_dev` / `kiekje` productie); nooit rechtstreeks op
  productie testen.

## 5. Acceptatietest (voorbeeld)

**Testgeval: kiekje plaatsen**

| Stap | Actie | Verwacht resultaat |
|------|-------|---------------------|
| 1 | Log in met geldig account | Feed wordt getoond |
| 2 | Klik op "+" | Upload-scherm verschijnt |
| 3 | Kies een geldige afbeelding en typ bijschrift | Voorbeeld wordt getoond |
| 4 | Klik "Plaatsen" | Kiekje verschijnt bovenaan eigen profielgrid en feed |
| 5 | Probeer te plaatsen zonder afbeelding | Foutmelding "Afbeelding is verplicht" |

Acceptatiecriteria zijn gebaseerd op de userstory uit de backlog en worden
afgetekend door de productowner/teamlead vóór een sprint als "done" geldt.

## 6. AVG (privacy)

- Er worden alleen noodzakelijke persoonsgegevens opgeslagen: gebruikersnaam,
  e-mailadres en een wachtwoord-**hash** (nooit het wachtwoord zelf).
- Gebruikers kunnen hun account en bijbehorende gegevens laten verwijderen
  (recht op vergetelheid) — technisch afgedwongen via `ON DELETE CASCADE`
  in het databaseschema, zodat ook alle posts/likes/comments meegaan.
- Geen gegevens worden gedeeld met derden zonder toestemming.
- Bij een echte livegang: een privacyverklaring publiceren en een
  verwerkersovereenkomst afsluiten met de hostingpartij.

## 7. Copyright

- Gebruikte lettertypen (Fraunces, Space Grotesk) zijn vrij te gebruiken
  (Google Fonts, open licentie).
- Voorbeeldfoto's in de demo komen van Unsplash (vrij van rechten voor
  gebruik); bij een live product uploaden gebruikers uitsluitend eigen content.
- Gebruikers blijven eigenaar van hun eigen geüploade foto's; in de
  gebruiksvoorwaarden wordt vastgelegd dat Kiekje enkel een gebruiksrecht
  krijgt om de foto te tonen binnen het platform.
- De merknaam en het uiterlijk zijn bewust anders dan bestaande social-media
  merken om geen inbreuk te maken op bestaand beeldmerk/auteursrecht.

## 8. Ethiek

- **Contentmoderatie**: bij een echte lancering is een meldknop en
  moderatiebeleid nodig tegen ongepaste content en pesten.
- **Verslavend ontwerp vermijden**: bewust geen oneindige auto-refresh of
  opzichtige "vind-ik-leuk"-tellers die vergelijking stimuleren; likes worden
  neutraal getoond.
- **Transparantie**: gebruikers weten welke gegevens worden opgeslagen (zie
  AVG-sectie) en waarom.
- **Toegankelijkheid** (accessibility): voldoende kleurcontrast, altijd
  `alt`-teksten bij afbeeldingen, en bedienbaar met toetsenbord.

## 9. Netwerk & bestandssystemen

- De backend communiceert via HTTP(S) REST-endpoints (`/api/...`) tussen
  frontend (React/Vue) en server (PHP), met JSON als uitwisselformaat.
- Geüploade foto's worden idealiter buiten de webroot of in objectopslag
  (bv. S3-achtige dienst) bewaard, met alleen de bestandsnaam/URL in de
  database (`posts.image_url`) — zo blijft bestandssysteem en database
  gescheiden en herstelbaar bij een schijfstoring.

## 10. Overzicht gedekte rubriek-onderdelen door dit project

Gesprek met teamlead, op tijd aan de bel trekken, scheiding test/live,
storypoints, React, Vue, AVG, bestandssystemen,
copyright, ethiek, netwerk, acceptatietest, ERD, klassendiagram,
usecase-diagram, wireframe, activity diagram, sitemap, CSS, HTML, JavaScript,
MySQL, PHP, basisvaardigheden programmeren, codestructuur, gebruikte libraries,
hash & salt, OOP, projectstructuur, SEO-vriendelijke opbouw (semantische HTML,
`<title>`/meta), unit test, voorkomen SQL-injecties, backlog, gesprektechnieken,
MoSCoW, presentatietechnieken, reflecteren, retrospective, scrum-board,
userstories, versiebeheer, werken in sprints, werkt op tijd, accessibility,
gerealiseerde functionaliteit.

*Niet meegenomen op uitdrukkelijk verzoek: Python, C# en Laravel (puur PHP i.p.v. framework).*
