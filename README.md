# 📚 Kursplanung WebApp

Eine Web-Anwendung zur Verwaltung von Kursen, Mitarbeitern und Trainern, entwickelt mit **PHP** und **PostgreSQL**.

## 🚀 Technologien

- PHP
- PostgreSQL
- HTML5
- CSS3
- Git & GitHub

---

## ✨ Funktionen

- Benutzeranmeldung
- Dashboard
- Mitarbeiterverwaltung
- Mitarbeiter anlegen
- Mitarbeiter bearbeiten
- Mitarbeiter aktivieren/deaktivieren
- Kursplan anzeigen
- Rollenverwaltung

---

## 📂 Projektstruktur

```
kursplanung-app/
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

---

## 🗄️ Datenbank

Die Datenbank befindet sich im Ordner

```
sql/database.sql
```

Vor dem Start der Anwendung muss diese Datenbank in PostgreSQL importiert werden.

---

## ⚙️ Installation

1. Repository klonen

```bash
git clone https://github.com/Abdennour945/kursplanung-webapp.git
```

2. PostgreSQL-Datenbank erstellen

3. `sql/database.sql` importieren

4. `config/db.example.php` nach `config/db.php` kopieren

5. In `config/db.php` die eigenen Daten eintragen:

```php
$host = "localhost";
$dbname = "kursplanung_app";
$user = "dein_username";
$password = "dein_passwort";
```

6. Projekt im Browser starten.

---

## 📸 Screenshots

### 🔐 Login

*(Screenshot hier später einfügen)*

### 🏠 Dashboard

*(Screenshot hier später einfügen)*

### 👥 Mitarbeiterverwaltung

*(Screenshot hier später einfügen)*

---

## 👨‍💻 Autor

Abdennour Bencharda

---

## 📖 Was ich in diesem Projekt gelernt habe

- Arbeiten mit PHP
- PostgreSQL-Datenbanken
- Benutzeranmeldung
- CRUD-Operationen
- Git und GitHub
- Projektstruktur für Web-Anwendungen
