import { Component, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from './core/auth.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './app.component.html',
})
export class AppComponent {
  auth = inject(AuthService);
  private router = inject(Router);

  menuOpen = signal(false);

  logout(): void {
    this.auth.logout().subscribe(() => {
      this.menuOpen.set(false);
      this.router.navigateByUrl('/login');
    });
  }
}
