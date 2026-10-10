// Tipovi podataka koje vraca Laravel API (odgovaraju tabelama iz baze).

export type Role = 'admin' | 'operater' | 'kontrolor' | 'gradjanin';

export interface User {
  id: number;
  name: string;
  email: string;
  role: Role;
  status: string;
  phone: string | null;
}

export interface UserBrief {
  id: number;
  name: string;
}

export interface LoginResponse {
  token: string;
  user: User;
}

/** Laravel paginate() odgovor */
export interface Page<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface Zone {
  id: number;
  name: string;
  location: string;
  tariff_per_hour: number;
  max_duration_minutes: number | null;
  work_hours_from: string;
  work_hours_to: string;
  type: string;
  spots_total: number;
  spots_free: number;
}

export interface ZoneBrief {
  id: number;
  name: string;
  tariff_per_hour?: number;
  max_duration_minutes?: number | null;
}

export type VehicleType = 'putnicko' | 'dostavno' | 'motocikl';

export interface Vehicle {
  id: number;
  plate: string;
  brand: string;
  model: string;
  type?: VehicleType;
  year?: number | null;
  owner?: UserBrief;
}

export interface NewVehicle {
  plate: string;
  brand: string;
  model: string;
  type: VehicleType;
  year: number | null;
}

export type TicketStatus = 'aktivna' | 'istekla' | 'otkazana';
export type Channel = 'app' | 'sms' | 'automat';

export interface Ticket {
  id: number;
  channel: Channel;
  start_time: string;
  end_time: string;
  price: number;
  status: TicketStatus;
  qr_code: string | null;
  vehicle?: Vehicle;
  zone?: ZoneBrief;
  user?: UserBrief;
}

export interface NewTicket {
  vehicle_id: number;
  zone_id: number;
  channel: Channel;
  duration_minutes: number;
}

export type ControlResult = 'ispravno' | 'neplaceno' | 'isteklo';
export type FineStatus = 'neplacena' | 'placena' | 'u_postupku';
export type PaymentMethod = 'gotovina' | 'kartica' | 'online';

export interface ActiveTicket {
  id: number;
  start_time: string;
  end_time: string;
  price: number;
  status: TicketStatus;
  zone?: ZoneBrief;
}

export interface ActivePermit {
  id: number;
  plate: string;
  valid_from: string;
  valid_until: string;
  zone?: ZoneBrief;
}

export interface LookupResult {
  vehicle: Vehicle;
  active_ticket: ActiveTicket | null;
  active_permit: ActivePermit | null;
  suggested_result: ControlResult;
}

export interface Fine {
  id: number;
  control_id: number;
  amount: number;
  status: FineStatus;
  payment_method: PaymentMethod | null;
  issued_at: string;
  paid_at: string | null;
  vehicle?: Vehicle;
  control?: {
    id: number;
    controller_id: number;
    location: string;
    timestamp: string;
    result: ControlResult;
  };
}

export interface Control {
  id: number;
  location: string;
  timestamp: string;
  result: ControlResult;
  vehicle?: Vehicle;
  fine?: Fine | null;
  controller?: UserBrief;
}

export interface NewControl {
  vehicle_id: number;
  location: string;
  result: ControlResult;
}
