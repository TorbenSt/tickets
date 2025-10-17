# Ticket System

Ein Laravel-basiertes Technical Support Ticket System für Digitalisierungsaufgaben und Bug Reports.

## 🚀 Technologie Stack

- **Laravel 12.32.5** - PHP Framework
- **Livewire 3** - Full-stack Framework für Laravel
- **Tailwind CSS 4.1.11** - Utility-first CSS Framework
- **SQLite** - Leichtgewichtige Datenbank für Entwicklung
- **Vite** - Frontend Build Tool

## 📋 Features

### Rollenbasierte Zugriffskontrolle
- **Developer**: Systemweiter Zugriff auf alle Tickets und Firmen
- **Customer**: Zugriff nur auf eigene Firma-Projekte und Tickets

### Ticket-Workflow mit Freigabeprozess
- **Developer erstellt Ticket** → Status "Benötigt Bestätigung" (OPEN)
- **Customer sieht Tickets zur Freigabe** → Kann Tickets freigeben
- **Customer erstellt Ticket** → Status "To Do" (TODO) - automatisch freigegeben
- **Kanban-Workflow**: OPEN → TODO → IN_PROGRESS → REVIEW → DONE

### Projekt- und User-Management
- Multi-Tenant Architektur mit Firma-basierter Isolation
- Projekt-Mitglieder Management mit Pivot-Tabelle
- Nur Projekt-Ersteller und Developer können User verwalten

### Prioritäts-System
- **Überprüfung** (1) - Allgemeine Anfragen
- **Normal** (2) - Standard Features/Bugs  
- **ASAP** (3) - Wichtige Features/Bugs
- **Notfall** (4) - Kritische Probleme, Sicherheit, Ausfälle

## 🏗️ Architektur

### Database Schema
```
Users (id, name, email, firma_id, role)
├── Firmas (id, name, email, phone)
├── Projects (id, name, description, firma_id, created_by)
│   └── project_user (project_id, user_id) [Pivot]
└── Tickets (id, title, description, status, priority, project_id, created_by, assigned_to)
```

### Routing Structure

**Customer Routes** (`/projects/*`, `/tickets/pending-approval`)
- Projekt-Erstellung und -Verwaltung
- Ticket-Erstellung in eigenen Projekten
- Freigabe von Developer-Tickets

**Developer Routes** (`/firmas/*`, `/tickets/emergency`)
- Firmen-Übersicht und -Details
- Systemweite Ticket-Verwaltung
- Notfall-Tickets (Priorität 4)

**Shared Routes** (`/tickets/{id}`, `/dashboard`)
- Ticket-Details mit rollenbasierter Authorization
- Dashboard mit Statistiken

## 🚦 Workflow: Ticket-Freigabeprozess

1. **Developer** erstellt Ticket für Kunde
   - Automatischer Status: `OPEN` (Benötigt Bestätigung)
   - Ticket erscheint in Customer's "Zur Freigabe" Liste

2. **Customer** prüft Tickets zur Freigabe
   - Navigation: "Zur Freigabe" im Sidebar-Menü
   - Übersicht aller OPEN-Status Tickets
   - Details-Ansicht und Freigabe-Button

3. **Customer** gibt Ticket frei
   - Status ändert sich: `OPEN` → `TODO`
   - Ticket kann nun von Developern bearbeitet werden

4. **Alternatively**: Customer erstellt eigene Tickets
   - Automatischer Status: `TODO` (bereits freigegeben)
   - Keine Freigabe durch Developer nötig

## 🛠️ Installation & Setup

```bash
# Repository klonen
git clone <repository-url>
cd tickets

# Dependencies installieren
composer install
npm install

# Environment konfigurieren
cp .env.example .env
php artisan key:generate

# Database & Seeding
php artisan migrate
php artisan db:seed --class=TicketSystemSeeder

# Assets builden
npm run build

# Development Server starten
php artisan serve
```

### Test Accounts

Nach dem Seeding stehen folgende Test-Accounts zur Verfügung:

**Developer Account:**
- Email: `developer@test.com`
- Password: `password`

**Customer Accounts:**
- Email: `customer@test.com` (Firma 1)
- Email: `customer2@test.com` (Firma 2)
- Password: `password`

## 🔧 Entwicklung

### Key Models & Methods

```php
// User Role Checks
$user->role->isDeveloper()
$user->role->isCustomer()

// Project Access
$project->hasUser($user)
$project->users() // BelongsToMany relationship

// Ticket Permissions
$ticket->canBeEditedBy($user)
$ticket->project->hasUser($user)

// Firma Relationships
$firma->projects()
$firma->tickets() // HasManyThrough projects
```

### Enums & Business Logic

```php
// Ticket Status Flow
TicketStatus::OPEN      // Benötigt Bestätigung (orange)
TicketStatus::TODO      // To Do (gray)
TicketStatus::IN_PROGRESS // In Bearbeitung (blue)
TicketStatus::REVIEW    // Review (yellow) 
TicketStatus::DONE      // Fertig (green)

// Priority System
TicketPriority::UEBERPRUFUNG // Überprüfung (1)
TicketPriority::NORMAL       // Normal (2)
TicketPriority::ASAP         // ASAP (3)
TicketPriority::NOTFALL      // Notfall (4)
```

### Security Features

- **Multi-Tenant Isolation**: Customers nur Zugriff auf eigene Firma
- **Project-based Authorization**: Ticket-Zugriff basiert auf Projekt-Mitgliedschaft
- **Role-based Middleware**: Automatische Route-Protection
- **CSRF Protection**: Alle Formulare geschützt
- **Model Authorization**: Controller-Level Permission Checks

## 📊 Monitoring & Analytics

Das System bietet Dashboard-Statistiken für:
- Ticket-Counts nach Status
- Projekt-Mitglieder Anzahl  
- Offene Tickets pro Firma/Projekt
- Priority-basierte Ticket-Verteilung

## 🔒 Security Considerations

- Alle User-Eingaben validiert und escaped
- SQL-Injection Prevention durch Eloquent ORM
- XSS Protection durch Blade Template Engine
- Route Model Binding mit automatischer Authorization
- Session-based Authentication mit CSRF Token

## 📝 Contributing

1. Feature Branch erstellen (`git checkout -b feature/amazing-feature`)
2. Changes committen (`git commit -m 'Add amazing feature'`)
3. Branch pushen (`git push origin feature/amazing-feature`)
4. Pull Request erstellen

## 📄 License

Dieses Projekt ist unter der MIT License lizenziert.