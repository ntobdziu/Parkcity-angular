import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { FineService } from '../../core/api.services';
import { AuthService } from '../../core/auth.service';
import { errorMessage } from '../../core/http-error';
import { Fine, Page, PaymentMethod } from '../../core/models';
import { PagerComponent } from '../../shared/pager.component';
import { StatusBadgeComponent } from '../../shared/status-badge.component';

@Component({
  selector: 'app-fines',
  standalone: true,
  imports: [DatePipe, DecimalPipe, FormsModule, PagerComponent, StatusBadgeComponent],
  templateUrl: './fines.component.html',
})
export class FinesComponent implements OnInit {
  auth = inject(AuthService);
  private fines = inject(FineService);

  result = signal<Page<Fine> | null>(null);
  loading = signal(false);
  error = signal('');
  message = signal('');

  statusFilter = '';
  currentPage = 1;

  // naplata: id kazne koja se trenutno naplacuje i izabran nacin placanja
  payingId = signal<number | null>(null);
  paymentMethod: PaymentMethod = 'gotovina';

  ngOnInit(): void {
    this.load();
  }

  /** Samo kontrolor i admin naplacuju kazne (gradjanin ih samo vidi) */
  canCollect(): boolean {
    return this.auth.hasRole('kontrolor', 'admin');
  }

  load(): void {
    this.loading.set(true);
    this.error.set('');

    this.fines.list(this.currentPage, this.statusFilter).subscribe({
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

  changeFilter(): void {
    this.currentPage = 1;
    this.load();
  }

  goToPage(page: number): void {
    this.currentPage = page;
    this.load();
  }

  startPay(fine: Fine): void {
    this.payingId.set(fine.id);
    this.paymentMethod = 'gotovina';
    this.message.set('');
    this.error.set('');
  }

  confirmPay(fine: Fine): void {
    this.fines.pay(fine.id, this.paymentMethod).subscribe({
      next: () => {
        this.payingId.set(null);
        this.message.set(`Kazna #${fine.id} je evidentirana kao plaćena.`);
        this.load();
      },
      error: (err) => this.error.set(errorMessage(err)),
    });
  }
}
