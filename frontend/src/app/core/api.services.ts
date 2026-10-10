import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { API_URL } from './api.config';
import {
  Control,
  Fine,
  LookupResult,
  NewControl,
  NewTicket,
  NewVehicle,
  Page,
  PaymentMethod,
  Ticket,
  Vehicle,
  Zone,
} from './models';

/** Zone (GET /api/zones) */
@Injectable({ providedIn: 'root' })
export class ZoneService {
  private http = inject(HttpClient);

  list(): Observable<Zone[]> {
    return this.http.get<Zone[]>(`${API_URL}/zones`);
  }
}

/** Vozila prijavljenog gradjanina */
@Injectable({ providedIn: 'root' })
export class VehicleService {
  private http = inject(HttpClient);

  list(): Observable<Vehicle[]> {
    return this.http.get<Vehicle[]>(`${API_URL}/vehicles`);
  }

  create(vehicle: NewVehicle): Observable<Vehicle> {
    return this.http.post<Vehicle>(`${API_URL}/vehicles`, vehicle);
  }
}

/** FUNKCIONALNOST 1: parking karte */
@Injectable({ providedIn: 'root' })
export class TicketService {
  private http = inject(HttpClient);

  list(page = 1, status = ''): Observable<Page<Ticket>> {
    let params = new HttpParams().set('page', page);
    if (status) {
      params = params.set('status', status);
    }
    return this.http.get<Page<Ticket>>(`${API_URL}/tickets`, { params });
  }

  get(id: number): Observable<Ticket> {
    return this.http.get<Ticket>(`${API_URL}/tickets/${id}`);
  }

  create(ticket: NewTicket): Observable<Ticket> {
    return this.http.post<Ticket>(`${API_URL}/tickets`, ticket);
  }

  extend(id: number, extraMinutes: number): Observable<Ticket> {
    return this.http.post<Ticket>(`${API_URL}/tickets/${id}/extend`, { extra_minutes: extraMinutes });
  }

  cancel(id: number): Observable<Ticket> {
    return this.http.post<Ticket>(`${API_URL}/tickets/${id}/cancel`, {});
  }
}

/** FUNKCIONALNOST 2a: kontrole vozila */
@Injectable({ providedIn: 'root' })
export class ControlService {
  private http = inject(HttpClient);

  lookup(plate: string): Observable<LookupResult> {
    return this.http.post<LookupResult>(`${API_URL}/controls/lookup`, { plate });
  }

  create(control: NewControl): Observable<Control> {
    return this.http.post<Control>(`${API_URL}/controls`, control);
  }

  list(page = 1): Observable<Page<Control>> {
    const params = new HttpParams().set('page', page);
    return this.http.get<Page<Control>>(`${API_URL}/controls`, { params });
  }
}

/** FUNKCIONALNOST 2b: kazne */
@Injectable({ providedIn: 'root' })
export class FineService {
  private http = inject(HttpClient);

  list(page = 1, status = ''): Observable<Page<Fine>> {
    let params = new HttpParams().set('page', page);
    if (status) {
      params = params.set('status', status);
    }
    return this.http.get<Page<Fine>>(`${API_URL}/fines`, { params });
  }

  pay(id: number, method: PaymentMethod): Observable<Fine> {
    return this.http.post<Fine>(`${API_URL}/fines/${id}/pay`, { payment_method: method });
  }
}
