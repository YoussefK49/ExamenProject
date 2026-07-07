# Kiekje 📸

Een Instagram-achtige webapp, gebouwd om zoveel mogelijk criteria uit de
meesterrubriek te raken — **zonder Python, C# en Laravel** (pure PHP).

## Snel starten

1. **Frontend (React, direct te openen)**
   → open `frontend/index.html` in de browser. Volledig werkende demo:
   inloggen, verhalenbalk, feed, liken, reageren, nieuw kiekje plaatsen
   (met live foto-voorbeeld), profielpagina met grid.

2. **Vue-onderdeel**
   → open `vue/stories-widget.html`. Losstaand Vue 3-component
   (verhalen-balk) om het Vue-criterium apart aan te tonen.

3. **Backend (PHP + MySQL)**
   ```bash
   mysql -u root -p < database/schema.sql
   php -S localhost:8000 -t backend
   ```
   Zie `backend/README.md` voor uitleg over de opzet.

4. **Unit test**
   ```bash
   vendor/bin/phpunit backend/tests/AuthTest.php
   ```

## Mapstructuur

```
kiekje/
  frontend/index.html          -> React-app (UI/UX, HTML, CSS, JS)
  vue/stories-widget.html      -> Vue-component
  backend/                     -> PHP API (MVC-achtig, PDO, hash & salt)
  database/schema.sql          -> MySQL-schema (ERD)
  diagrams/                    -> ERD, klassendiagram, use case, wireframe,
                                   activity diagram, sitemap (SVG)
  docs/documentatie.md         -> AVG, copyright, ethiek, scrum-artefacten,
                                   acceptatietest, versiebeheer
```

## Design

Eigen merk "Kiekje" (geen Instagram-logo/merk gebruikt i.v.m. auteursrecht),
met een analoge-filmrol-esthetiek: warme amber/roze accenten op een
donkere achtergrond, sprocket-gaatjes rond elke foto, Fraunces + Space
Grotesk als lettertypeparing.
