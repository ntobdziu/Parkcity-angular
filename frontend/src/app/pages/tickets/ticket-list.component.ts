import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { TicketService } from '../../core/api.services';
import { AuthService } from '../../core/auth.service';
import { errorMessage } from '../../core/http-error';
import { Page, Ticket } from '../../core/models';
import { PagerComponent } from '../../shared/pager.component';
import { StatusBadgeComponent } from '../../shared/status-badge.component';

@Component({
  selector: 'app-ticket-list',
  standalone: true,
  imports: [DatePipe, DecimalPipe, FormsModule, RouterLink, PagerComponent, StatusBadgeComponent],
  templateUrl: './ticket-list.component.html',
})
export class TicketListComponent implements OnInit {
  auth = inject(AuthService);
  private tickets = inject(TicketService);

  result = signal<Page<Ticket> | null>(null);
  loading = signal(false);
  error = signal('');
  message = signal('');

  statusFilter = '';
  currentPage = 1;

  // produzenje karte: id karte koja se trenutno produzava i broj dodatnih minuta
  extendingId = signal<number | null>(null);
  extraMinutes = 30;
  readonly extraOptions = [15, 30, 60, 120];

  ngOnInit(): void {
    this.load();
  }

  load(): void {
    this.loading.set(true);
    this.error.set('');

    this.tickets.list(this.currentPage, this.statusFilter).subscribe({
      next: (page) => {
        this.result.set(page);
        this.loading.set(false);
      },
      error: (err) => {
        this.error.set(errorMessage(err));
        this.loading.set(false);
      },
    });
  }

  changeFilter(): void {
    this.currentPage = 1;
    this.load();
  }

  goToPage(page: number): void {
    this.currentPage = page;
    this.load();
  }

  startExtend(ticket: Ticket): void {
    this.extendingId.set(ticket.id);
    this.extraMinutes = 30;
    this.message.set('');
    this.error.set('');
  }

  confirmExtend(ticket: Ticket): void {
    this.tickets.extend(ticket.id, this.extraMinutes).subscribe({
      next: () => {
        this.extendingId.set(null);
        this.message.set('Trajanje parkinga je produženo.');
        this.load();
      },
      error: (err) => this.error.set(errorMessage(err)),
    });
  }

  cancel(ticket: Ticket): void {
    if (!confirm('Da li sigurno želiš da otkažeš ovu kartu?')) {
      return;
    }

    this.message.set('');
    this.error.set('');

    this.tickets.cancel(ticket.id).subscribe({
      next: () => {
        this.message.set('Parking karta je otkazana.');
        this.load();
      },
      error: (err) => this.error.set(errorMessage(err)),
    });
  }
}
