import { Component, computed, input } from '@angular/core';

const LABELS: Record<string, string> = {
  aktivna: 'Aktivna',
  istekla: 'Istekla',
  otkazana: 'Otkazana',
  ispravno: 'Ispravno',
  neplaceno: 'Neplaćeno',
  isteklo: 'Isteklo',
  neplacena: 'Neplaćena',
  placena: 'Plaćena',
  u_postupku: 'U postupku',
};

const COLORS: Record<string, string> = {
  aktivna: 'success',
  istekla: 'secondary',
  otkazana: 'danger',
  ispravno: 'success',
  neplaceno: 'danger',
  isteklo: 'warning',
  neplacena: 'danger',
  placena: 'success',
  u_postupku: 'warning',
};

/** Obojena oznaka statusa: <app-status-badge [status]="ticket.status" /> */
@Component({
  selector: 'app-status-badge',
  standalone: true,
  template: `<span [class]="cssClass()">{{ label() }}</span>`,
})
export class StatusBadgeComponent {
  status = input.required<string>();

  label = computed(() => LABELS[this.status()] ?? this.status());
  cssClass = computed(() => 'badge text-bg-' + (COLORS[this.status()] ?? 'secondary'));
}
