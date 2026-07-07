# Kiekje backend

## Structuur
```
backend/
  config/database.php     -> PDO-verbinding
  api/auth.php             -> registreren & inloggen (hash & salt)
  api/posts.php            -> feed ophalen & kiekje plaatsen
  api/interactions.php     -> like/unlike & reacties
  tests/AuthTest.php        -> voorbeeld unit test (PHPUnit)
database/schema.sql        -> MySQL-schema (ERD)
```

Lokaal draaien (met PHP's ingebouwde server):
```bash
mysql -u root -p < database/schema.sql
php -S localhost:8000 -t backend
```

## Over de architectuur
Deze backend is bewust in **vanilla PHP + PDO** geschreven (geen framework).
Hij volgt wel een duidelijk MVC-achtig patroon: config gescheiden van
endpoints, endpoints gescheiden van datatoegang, zodat de projectstructuur
overzichtelijk en uitbreidbaar blijft.

## Beveiliging in dit project
- **Hash & Salt**: `password_hash()` (bcrypt, cost 12) — nooit platte wachtwoorden.
- **SQL-injecties voorkomen**: overal PDO prepared statements met `?`-placeholders,
  `PDO::ATTR_EMULATE_PREPARES => false` zodat de query écht op databaseniveau
  wordt voorbereid.
- **Scheiding test/livesomgeving**: gebruik losse `.env`-waarden voor
  `DB_HOST`/`DB_NAME` per omgeving (zie `config/database.php`, leest via `getenv()`).
  Draai lokaal tegen een aparte `kiekje_test`-database, nooit tegen productie.
