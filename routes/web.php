<?php

use App\Http\Controllers\{FirmaController, ProjectController, TicketController};
use Illuminate\Support\Facades\{Auth, Route};
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - role-based redirect
    Route::get('/dashboard', function () {
        return Auth::user()->role->isDeveloper() 
            ? redirect()->route('tickets.index')
            : redirect()->route('projects.index');
    })->name('dashboard');
    
    // Customer routes
    Route::middleware(['role:customer'])->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        
        Route::get('/projects/{project}/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        
        // Customer-spezifische Ticket-Funktionen
        Route::get('/tickets/pending-approval', [TicketController::class, 'pendingApproval'])->name('tickets.pending-approval');
        Route::patch('/tickets/{ticket}/approve', [TicketController::class, 'approve'])->name('tickets.approve');
    });
    
    // Developer routes
    Route::middleware(['role:developer'])->group(function () {
        // Firmen-Management
        Route::get('/firmas', [FirmaController::class, 'index'])->name('firmas.index');
        Route::get('/firmas/{firma}', [FirmaController::class, 'show'])->name('firmas.show');
        
        // Developer-spezifische Ticket-Views
        Route::get('/tickets/emergency', [TicketController::class, 'emergency'])->name('tickets.emergency');
        
        // Ticket-Management
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    });
    
    // Shared routes (beide Rollen)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    
    // Project User Management (accessible by project creators and developers)
    Route::get('/projects/{project}/users', [ProjectController::class, 'users'])->name('projects.users');
    Route::post('/projects/{project}/users', [ProjectController::class, 'addUser'])->name('projects.add-user');
    Route::delete('/projects/{project}/users/{user}', [ProjectController::class, 'removeUser'])->name('projects.remove-user');
    
    // API for user search in project context (for AJAX)
    Route::get('/api/projects/{project}/available-users', [ProjectController::class, 'availableUsers'])->name('api.projects.available-users');
});

// Settings routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Route::get('/logout', function () { return redirect()->route('login'); })->name('logout.redirect');

    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// iframe Integration Routes
Route::prefix('iframe')->middleware(['iframe.auth', 'iframe.csrf'])->group(function () {
    
    // iframe Dashboard - role-based redirect  
    Route::get('/dashboard', [App\Http\Controllers\IframeController::class, 'dashboard'])->name('iframe.dashboard');
    Route::get('/', [App\Http\Controllers\IframeController::class, 'dashboard']); // alias for root
    Route::get('/debug', [App\Http\Controllers\IframeController::class, 'debug'])->name('iframe.debug');
    
    // Customer iframe routes
    Route::middleware(['role:customer'])->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('iframe.projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('iframe.projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('iframe.projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('iframe.projects.show');
        Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('iframe.projects.update');
        
        Route::get('/projects/{project}/tickets/create', [TicketController::class, 'create'])->name('iframe.tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('iframe.tickets.store');
        Route::get('/tickets/pending-approval', [TicketController::class, 'pendingApproval'])->name('iframe.tickets.pending-approval');
        Route::patch('/tickets/{ticket}/approve', [TicketController::class, 'approve'])->name('iframe.tickets.approve');
        
        // Project User Management für iframe
        Route::get('/projects/{project}/users', [ProjectController::class, 'users'])->name('iframe.projects.users');
        Route::post('/projects/{project}/users', [ProjectController::class, 'addUser'])->name('iframe.projects.add-user');
        Route::delete('/projects/{project}/users/{user}', [ProjectController::class, 'removeUser'])->name('iframe.projects.remove-user');
    });
    
    // Developer iframe routes  
    Route::middleware(['role:developer'])->group(function () {
        Route::get('/tickets', [TicketController::class, 'index'])->name('iframe.tickets.index');
        Route::get('/tickets/emergency', [TicketController::class, 'emergency'])->name('iframe.tickets.emergency');
        Route::get('/firmas', [FirmaController::class, 'index'])->name('iframe.firmas.index');
        Route::get('/firmas/{firma}', [FirmaController::class, 'show'])->name('iframe.firmas.show');
        
        // Ticket-Management für iframe
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('iframe.tickets.assign');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('iframe.tickets.update-status');
    });
    
    // Shared iframe routes
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('iframe.tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('iframe.tickets.edit');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('iframe.tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('iframe.tickets.destroy');
    
    // API für iframe
    Route::get('/api/projects/{project}/available-users', [ProjectController::class, 'availableUsers'])->name('iframe.api.projects.available-users');
});

// GET-basierte iFrame-Authentifizierung (public, außerhalb der auth-Middleware)
Route::get('/iframe/login', [App\Http\Controllers\IframeLoginController::class, 'login'])->name('iframe.login');

require __DIR__.'/auth.php';
