import { DecimalPipe } from '@angular/common';
import { Component, OnInit, computed, inject, signal } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { map, startWith } from 'rxjs';
import { TicketService, VehicleService, ZoneService } from '../../core/api.services';
import { errorMessage } from '../../core/http-error';
import { Channel, Vehicle, VehicleType, Zone } from '../../core/models';

@Component({
  selector: 'app-ticket-form',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, DecimalPipe],
  templateUrl: './ticket-form.component.html',
})
export class TicketFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private ticketService = inject(TicketService);
  private vehicleService = inject(VehicleService);
  private zoneService = inject(ZoneService);

  vehicles = signal<Vehicle[]>([]);
  zones = signal<Zone[]>([]);
  saving = signal(false);
  error = signal('');

  // mala forma za dodavanje vozila (da gradjanin bez vozila moze da kupi kartu)
  showVehicleForm = signal(false);
  vehicleError = signal('');
  savingVehicle = signal(false);

  readonly quickDurations = [30, 60, 120, 180];

  form = this.fb.group({
    vehicle_id: this.fb.control<number | null>(null, Validators.required),
    zone_id: this.fb.control<number | null>(null, Validators.required),
    channel: this.fb.nonNullable.control<Channel>('app', Validators.required),
    duration_minutes: this.fb.nonNullable.control(60, [Validators.required, Validators.min(15), Validators.max(1440)]),
  });

  vehicleForm = this.fb.nonNullable.group({
    plate: ['', [Validators.required, Validators.maxLength(20)]],
    brand: ['', Validators.required],
    model: ['', Validators.required],
    type: this.fb.nonNullable.control<VehicleType>('putnicko'),
    year: this.fb.control<number | null>(null),
  });

  /** Trenutne vrednosti forme kao signal -> cena se racuna uzivo dok korisnik bira */
  private values = toSignal(
    this.form.valueChanges.pipe(
      map(() => this.form.getRawValue()),
      startWith(this.form.getRawValue())
    ),
    { requireSync: true }
  );

  selectedZone = computed(() => {
    const zoneId = Number(this.values().zone_id);
    return this.zones().find((z) => z.id === zoneId) ?? null;
  });

  maxDuration = computed(() => this.selectedZone()?.max_duration_minutes ?? 1440);

  durationTooLong = computed(() => Number(this.values().duration_minutes) > this.maxDuration());

  /** cena = tarifa po satu x minuti / 60 (isto kao na serveru) */
  price = computed(() => {
    const zone = this.selectedZone();
    const minutes = Number(this.values().duration_minutes);

    if (!zone || !minutes || minutes < 0) {
      return 0;
    }
    return Math.round(zone.tariff_per_hour * (minutes / 60) * 100) / 100;
  });

  ngOnInit(): void {
    this.zoneService.list().subscribe({
      next: (zones) => this.zones.set(zones),
      error: (err) => this.error.set(errorMessage(err)),
    });

    this.vehicleService.list().subscribe({
      next: (vehicles) => {
        this.vehicles.set(vehicles);
        if (vehicles.length === 0) {
          this.showVehicleForm.set(true);
        } else if (vehicles.length === 1) {
          this.form.controls.vehicle_id.setValue(vehicles[0].id);
        }
      },
      error: (err) => this.error.set(errorMessage(err)),
    });
  }

  setDuration(minutes: number): void {
    this.form.controls.duration_minutes.setValue(minutes);
  }

  addVehicle(): void {
    if (this.vehicleForm.invalid) {
      this.vehicleForm.markAllAsTouched();
      return;
    }

    this.savingVehicle.set(true);
    this.vehicleError.set('');

    this.vehicleService.create(this.vehicleForm.getRawValue()).subscribe({
      next: (vehicle) => {
        this.vehicles.update((list) => [...list, vehicle]);
        this.form.controls.vehicle_id.setValue(vehicle.id);
        this.vehicleForm.reset({ type: 'putnicko' });
        this.showVehicleForm.set(false);
        this.savingVehicle.set(false);
      },
      error: (err) => {
        this.vehicleError.set(errorMessage(err));
        this.savingVehicle.set(false);
      },
    });
  }

  submit(): void {
    if (this.form.invalid || this.durationTooLong()) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();
    this.saving.set(true);
    this.error.set('');

    this.ticketService
      .create({
        vehicle_id: Number(value.vehicle_id),
        zone_id: Number(value.zone_id),
        channel: value.channel,
        duration_minutes: Number(value.duration_minutes),
      })
      .subscribe({
        next: (ticket) => this.router.navigate(['/tickets', ticket.id], { queryParams: { novo: 1 } }),
        error: (err) => {
          this.error.set(errorMessage(err));
          this.saving.set(false);
        },
      });
  }
}
