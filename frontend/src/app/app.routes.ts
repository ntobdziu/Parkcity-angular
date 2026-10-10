import { inject } from '@angular/core';
import { Routes } from '@angular/router';
import { AuthService } from './core/auth.service';
import { authGuard, guestGuard, roleGuard } from './core/guards';
import { LoginComponent } from './pages/auth/login.component';
import { RegisterComponent } from './pages/auth/register.component';
import { ControlHistoryComponent } from './pages/controls/control-history.component';
import { ControlsShellComponent } from './pages/controls/controls-shell.component';
import { FinesComponent } from './pages/controls/fines.component';
import { VehicleCheckComponent } from './pages/controls/vehicle-check.component';
import { TicketDetailComponent } from './pages/tickets/ticket-detail.component';
import { TicketFormComponent } from './pages/tickets/ticket-form.component';
import { TicketListComponent } from './pages/tickets/ticket-list.component';

export const routes: Routes = [
  { path: 'login', component: LoginComponent, canActivate: [guestGuard] },
  { path: 'register', component: RegisterComponent, canActivate: [guestGuard] },

  // ===== FUNKCIONALNOST 1: PARKING KARTE =====
  {
    path: 'tickets',
    canActivate: [authGuard],
    children: [
      { path: '', component: TicketListComponent },
      { path: 'new', component: TicketFormComponent, canActivate: [roleGuard('gradjanin')] },
      { path: ':id', component: TicketDetailComponent },
    ],
  },

  // ===== FUNKCIONALNOST 2: KONTROLA I KAZNE =====
  {
    path: 'controls',
    component: ControlsShellComponent,
    canActivate: [authGuard, roleGuard('gradjanin', 'kontrolor', 'admin')],
    children: [
      {
        path: '',
        pathMatch: 'full',
        // podrazumevani tab zavisi od uloge
        redirectTo: () => {
          const auth = inject(AuthService);
          if (auth.hasRole('kontrolor')) {
            return 'check';
          }
          return auth.hasRole('admin') ? 'history' : 'fines';
        },
      },
      { path: 'check', component: VehicleCheckComponent, canActivate: [roleGuard('kontrolor')] },
      { path: 'history', component: ControlHistoryComponent, canActivate: [roleGuard('kontrolor', 'admin')] },
      { path: 'fines', component: FinesComponent },
    ],
  },

  { path: '', pathMatch: 'full', redirectTo: 'tickets' },
  { path: '**', redirectTo: 'tickets' },
];
