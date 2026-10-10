import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { interval } from 'rxjs';
import { TicketService } from '../../core/api.services';
import { errorMessage } from '../../core/http-error';
import { Ticket } from '../../core/models';
import { StatusBadgeComponent } from '../../shared/status-badge.component';

@Component({
  selector: 'app-ticket-detail',
  standalone: true,
  imports: [DatePipe, DecimalPipe, RouterLink, StatusBadgeComponent],
  templateUrl: './ticket-detail.component.html',
})
export class TicketDetailComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private tickets = inject(TicketService);

  ticket = signal<Ticket | null>(null);
  error = signal('');
  justBought = signal(false);

  /** "Sada" se osvezava na svakih 15 sekundi da preostalo vreme bude tacno */
  private now = signal(Date.now());

  remaining = computed(() => {
    const t = this.ticket();
    if (!t || t.status !== 'aktivna') {
      return '';
    }

    const minutes = Math.floor((new Date(t.end_time).getTime() - this.now()) / 60000);
    if (minutes <= 0) {
      return 'Vreme je isteklo';
    }

    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return h > 0 ? `${h} h ${m} min` : `${m} min`;
  });

  constructor() {
    interval(15000)
      .pipe(takeUntilDestroyed())
      .subscribe(() => this.now.set(Date.now()));
  }

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.justBought.set(this.route.snapshot.queryParamMap.has('novo'));

    this.tickets.get(id).subscribe({
      next: (ticket) => this.ticket.set(ticket),
      error: (err) => this.error.set(errorMessage(err, 'Karta nije pronađena.')),
    });
  }
}
