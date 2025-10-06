# Copilot Instructions for### Database Schema & Factories
- **Foreign Keys**: firma_id, project_id, created_by, assigned_to with proper cascade/set null
- **Pivot Table**: project_user for many-to-many User ↔ Project relationships  
- **Factory Patterns**: Use `User::factory()->developer()` vs `->customer()` states
- **Seeding**: TicketSystemSeeder creates realistic multi-tenant test data structure
- **Constraints**: Unique project_user combinations, proper cascade deletes

## Routing Architecture & Access Control

### Authentication & Authorization
- **Role Middleware**: `RoleMiddleware` with `role:customer` and `role:developer` guards
- **Dashboard Redirect**: Auto-redirect based on user role after login
  - Developers → `/tickets` (system-wide ticket overview)
  - Customers → `/projects` (own firma projects only)
- **Route Protection**: Role-specific middleware groups prevent unauthorized access

### Customer Routes (`role:customer`)
```php
/projects              # Own firma projects only
/projects/create       # Create new projects
/projects/{project}    # View project details + tickets
/projects/{project}/tickets/create  # Create tickets in own projects
```

### Developer Routes (`role:developer`)  
```php
/firmas                # All firmas overview
/firmas/{firma}        # Firma details with projects
/tickets               # System-wide tickets
/tickets/emergency     # Emergency priority tickets
/tickets/{ticket}/assign    # Assign tickets to developers
/tickets/{ticket}/status    # Update ticket status
```

### Shared Routes (both roles)
```php
/tickets/{ticket}      # View ticket details (with proper authorization)
PATCH /tickets/{ticket} # Update ticket (role-based permissions)
DELETE /tickets/{ticket} # Delete ticket (role-based permissions)
```

### Controller Structure
- **FirmaController**: Developer-only firma management
- **ProjectController**: Customer project management + developer oversight
- **TicketController**: Role-based ticket operations with specialized methods:
  - `index()` - All tickets (developers) vs firma tickets (customers)
  - `emergency()` - Priority 4 tickets for developers
  - `assign()` - Developer assignment functionality
  - `updateStatus()` - Workflow status changes

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
- **TicketStatus**: open → todo → in_progress → review → done (Kanban workflow)
  - `open` - Benötigt Bestätigung (orange)
  - `todo` - To Do (gray) 
  - `in_progress` - In Bearbeitung (blue)
  - `review` - Review (yellow)
  - `done` - Fertig (green)
- **TicketPriority**: überprüfung (1) → normal (2) → asap (3) → notfall (4) with order() method
- **UserRole**: customer (belongs to Firma) vs developer (cross-firma access)
  - `isDeveloper()` and `isCustomer()` helper methods
- Developers can edit all tickets, customers only their own within firma projects

### Security & Access Patterns
- **Multi-tenant**: Customers isolated to their firma's data
- **Cross-tenant**: Developers have system-wide access
- **Authorization**: Combine role checks with ownership validation
- **Route Model Binding**: Use with proper scoping for security