import { DatePipe, DecimalPipe } from '@angular/common';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ControlService } from '../../core/api.services';
import { errorMessage } from '../../core/http-error';
import { ControlResult, LookupResult } from '../../core/models';
import { StatusBadgeComponent } from '../../shared/status-badge.component';

/**
 * Kontrolor unese tablicu -> sistem pronadje vozilo i PREDLOZI rezultat
 * (ispravno / isteklo / neplaceno) -> kontrolor potvrdi i, ako treba, nastaje kazna.
 */
@Component({
  selector: 'app-vehicle-check',
  standalone: true,
  imports: [ReactiveFormsModule, DatePipe, DecimalPipe, StatusBadgeComponent],
  templateUrl: './vehicle-check.component.html',
})
export class VehicleCheckComponent {
  private fb = inject(FormBuilder);
  private controls = inject(ControlService);

  lookup = signal<LookupResult | null>(null);
  searching = signal(false);
  saving = signal(false);
  error = signal('');
  success = signal('');

  plateForm = this.fb.nonNullable.group({
    plate: ['', [Validators.required, Validators.maxLength(20)]],
  });

  confirmForm = this.fb.nonNullable.group({
    location: ['', [Validators.required, Validators.maxLength(255)]],
    result: this.fb.nonNullable.control<ControlResult>('ispravno', Validators.required),
  });

  search(): void {
    if (this.plateForm.invalid) {
      this.plateForm.markAllAsTouched();
      return;
    }

    this.searching.set(true);
    this.error.set('');
    this.success.set('');
    this.lookup.set(null);

    this.controls.lookup(this.plateForm.getRawValue().plate).subscribe({
      next: (res) => {
        this.lookup.set(res);
        this.confirmForm.controls.result.setValue(res.suggested_result);
        this.searching.set(false);
      },
      error: (err) => {
        this.error.set(errorMessage(err));
        this.searching.set(false);
      },
    });
  }

  confirm(): void {
    const res = this.lookup();

    if (!res || this.confirmForm.invalid) {
      this.confirmForm.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.error.set('');

    const { location, result } = this.confirmForm.getRawValue();

    this.controls.create({ vehicle_id: res.vehicle.id, location, result }).subscribe({
      next: (control) => {
        this.success.set(
          control.fine
            ? `Kontrola je evidentirana. Izdata je kazna od ${control.fine.amount} RSD.`
            : 'Kontrola je evidentirana. Vozilo je ispravno, kazna nije izdata.'
        );
        this.lookup.set(null);
        this.plateForm.reset({ plate: '' });
        this.saving.set(false);
      },
      error: (err) => {
        this.error.set(errorMessage(err));
        this.saving.set(false);
      },
    });
  }

  cancel(): void {
    this.lookup.set(null);
  }
}
