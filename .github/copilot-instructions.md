# Copilot Instructions for### Database Schema & Factories
- **Foreign Keys**: firma_id, project_id, created_by, assigned_to with proper cascade/set null
- **Pivot Table**: project_user for many-to-many User ↔ Project relationships  
- **Factory Patterns**: Use `User::factory()->developer()` vs `->customer()` states
- **Seeding**: TicketSystemSeeder creates realistic multi-tenant test data structure
- **Constraints**: Unique project_user combinations, proper cascade deletesnical Ticket System

## Project Overview
This is a Laravel-based technical support ticket system for digitalization tasks and bug reports. Built with Laravel 12, Livewire 3, Volt (single-file components), and Flux UI components.

## Domain Model & Business Logic

### Critical Files
- `app/Models/User.php` - Extended with firma relationship and role-based methods
- `app/Models/{Firma,Project,Ticket}.php` - Core domain models with business logic
- `app/Enums/{UserRole,TicketStatus,TicketPriority}.php` - Type-safe enums with helper methods
- `database/seeders/TicketSystemSeeder.php` - Realistic multi-tenant test data
- `database/factories/` - Factories with proper relationship handling and states

### Key Model Methods & Patterns
```php
// User authorization patterns
$user->isDeveloper() // Check role
$project->hasUser($user) // Check project access
$ticket->canBeEditedBy($user) // Permission check

// Relationships
$firma->tickets() // HasManyThrough Project
$user->assignedTickets() // Developer assignments
$project->users() // BelongsToMany with pivot
```

### Enums & Business Rules  
- **TicketStatus**: todo → in_progress → review → done (Kanban workflow)
- **TicketPriority**: überprüfung (1) → normal (2) → asap (3) → notfall (4) with order() method
- **UserRole**: customer (belongs to Firma) vs developer (cross-firma access)
- Developers can edit all tickets, customers only their own within firma projects