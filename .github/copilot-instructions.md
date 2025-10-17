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
/tickets/pending-approval  # View developer-created tickets requiring approval
PATCH /tickets/{ticket}/approve  # Approve tickets (OPEN → TODO)
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

### Controller Structure & User Management
- **FirmaController**: Developer-only firma management
- **ProjectController**: Customer project management + developer oversight + **User Management**
  - `users()` - Show project members + available users for assignment
  - `addUser()` - Add users to projects (with firma validation)
  - `removeUser()` - Remove users from projects (except creator)
  - `availableUsers()` - API endpoint for AJAX user selection
- **TicketController**: Role-based ticket operations with specialized methods:
  - `index()` - Project-based tickets (customers) vs all tickets (developers)
  - `pendingApproval()` - Customer-only view for tickets requiring approval
  - `approve()` - Customer approval workflow (OPEN → TODO status change)
  - `emergency()` - Priority 4 tickets for developers
  - `assign()` - Developer assignment functionality
  - `updateStatus()` - Workflow status changes

### Project-User Management System
- **Pivot Relationship**: `project_user` table manages many-to-many User ↔ Project
- **Auto-Assignment**: Project creator automatically becomes project member
- **Permission Levels**: Only project creators and developers can manage project users
- **Firma Scoping**: Users can only be added to projects within their firma (except developers)
- **Creator Protection**: Project creator cannot be removed from project
- **Ticket Access**: Users see only tickets from projects they're members of

### API Endpoints & AJAX Support
```php
GET /api/projects/{project}/available-users  # JSON list of assignable users
POST /projects/{project}/users              # Add user to project
DELETE /projects/{project}/users/{user}     # Remove user from project
```

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
  - `open` - Benötigt Bestätigung (orange) - Only for developer-created tickets
  - `todo` - To Do (gray) - Customer-approved or customer-created tickets
  - `in_progress` - In Bearbeitung (blue)
  - `review` - Review (yellow)
  - `done` - Fertig (green)
- **TicketPriority**: überprüfung (1) → normal (2) → asap (3) → notfall (4) with order() method
- **UserRole**: customer (belongs to Firma) vs developer (cross-firma access)
  - `isDeveloper()` and `isCustomer()` helper methods
- **Ticket Creation Logic**: Developer tickets start as OPEN (need approval), Customer tickets start as TODO (pre-approved)

### Security & Access Patterns
- **Multi-tenant**: Customers isolated to their firma's data via project membership
- **Cross-tenant**: Developers have system-wide access
- **Project-based Authorization**: 
  - Ticket access: `$ticket->project->hasUser($user)` 
  - Project management: Creator or Developer role required
  - User assignment: Validated against firma membership
- **Authorization Layers**: Role → Project Membership → Resource Ownership
- **Route Model Binding**: Use with proper scoping for security

### Model Helper Methods & Validation
```php
// Project access & user management
$project->hasUser($user)              # Check project membership
$project->users()                     # BelongsToMany relationship
$firma->availableUsersForProject($project)  # Get assignable users

// Ticket permissions (updated for project-based access)
$ticket->canBeEditedBy($user)         # Developer OR project member
$ticket->project->hasUser($user)      # Project membership check

// User role helpers
$user->role->isDeveloper()            # Cross-firma access
$user->role->isCustomer()             # Firma-scoped access
```