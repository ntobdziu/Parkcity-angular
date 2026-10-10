import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from '../../core/auth.service';

/** Okvir funkcionalnosti "Kontrola i kazne": tabovi + mesto za podstranicu (router-outlet) */
@Component({
  selector: 'app-controls-shell',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './controls-shell.component.html',
})
export class ControlsShellComponent {
  auth = inject(AuthService);
}
