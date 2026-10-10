import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { ControlService } from '../../core/api.services';
import { AuthService } from '../../core/auth.service';
import { errorMessage } from '../../core/http-error';
import { Control, Page } from '../../core/models';
import { PagerComponent } from '../../shared/pager.component';
import { StatusBadgeComponent } from '../../shared/status-badge.component';

@Component({
  selector: 'app-control-history',
  standalone: true,
  imports: [DatePipe, DecimalPipe, PagerComponent, StatusBadgeComponent],
  templateUrl: './control-history.component.html',
})
export class ControlHistoryComponent implements OnInit {
  auth = inject(AuthService);
  private controls = inject(ControlService);

  result = signal<Page<Control> | null>(null);
  loading = signal(false);
  error = signal('');

  ngOnInit(): void {
    this.load(1);
  }

  load(page: number): void {
    this.loading.set(true);
    this.error.set('');

    this.controls.list(page).subscribe({
      next: (res) => {
        this.result.set(res);
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(errorMessage(err));
        this.loading.set(false);
      },
    });
  }
}
