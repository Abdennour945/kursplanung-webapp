# Kursplanung WebApp

## Beschreibung

Dieses Projekt ist eine Webanwendung zur Verwaltung von Kursen, Mitarbeitern und Trainern.
Die Anwendung wurde im Rahmen eines Hochschulprojekts mit PHP und PostgreSQL entwickelt.

## Technologien

- PHP
- PostgreSQL
- HTML5
- CSS3
- Git
- GitHub

## Funktionen

- Benutzeranmeldung
- Dashboard
- Mitarbeiterverwaltung
- Mitarbeiter anlegen
- Mitarbeiter bearbeiten
- Mitarbeiter aktivieren und deaktivieren
- Kursplan anzeigen
- Rollenverwaltung

## Projektstruktur

```
kursplanung-webapp/
│
├── assets/
├── config/
├── includes/
├── public/
├── sql/
│   └── database.sql
├── src/
├── .gitignore
└── README.md
```

## Datenbank

Die Datenbank befindet sich im Ordner:

```
sql/database.sql
```

Vor dem Start der Anwendung muss diese Datei in PostgreSQL importiert werden.

## Installation

1. Repository klonen

```bash
git clone https://github.com/Abdennour945/kursplanung-webapp.git
```

2. Eine PostgreSQL-Datenbank erstellen.

3. Die Datei

```
sql/database.sql
```

importieren.

4. Die Datei

```
config/db.example.php
```

nach

```
config/db.php
```

kopieren.

5. In `config/db.php` die eigenen Datenbankzugangsdaten eintragen.

6. Das Projekt mit einem lokalen Webserver (z. B. XAMPP oder MAMP) starten.

## Screenshots

### Login

*(Screenshot hier einfügen)*

### Dashboard

*(Screenshot hier einfügen)*

### Mitarbeiterverwaltung

*(Screenshot hier einfügen)*

## Autor

Abdennour Bencharda

Informatikstudent
