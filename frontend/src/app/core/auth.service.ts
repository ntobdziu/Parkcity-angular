import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, catchError, of, tap } from 'rxjs';
import { API_URL } from './api.config';
import { LoginResponse, Role, User } from './models';

const TOKEN_KEY = 'parkcity_token';
const USER_KEY = 'parkcity_user';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);

  /** Trenutno prijavljeni korisnik (signal) - cuva se i u localStorage da prezivi refresh stranice */
  readonly user = signal<User | null>(this.readStoredUser());
  readonly isLoggedIn = computed(() => this.user() !== null);

  get token(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  login(email: string, password: string): Observable<LoginResponse> {
    return this.http
      .post<LoginResponse>(`${API_URL}/login`, { email, password })
      .pipe(tap((res) => this.save(res)));
  }

  register(data: {
    name: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
  }): Observable<LoginResponse> {
    return this.http
      .post<LoginResponse>(`${API_URL}/register`, data)
      .pipe(tap((res) => this.save(res)));
  }

  logout(): Observable<unknown> {
    return this.http.post(`${API_URL}/logout`, {}).pipe(
      catchError(() => of(null)),
      tap(() => this.clear())
    );
  }

  /** Brise token i korisnika iz memorije i iz localStorage */
  clear(): void {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    this.user.set(null);
  }

  hasRole(...roles: Role[]): boolean {
    const current = this.user();
    return current !== null && roles.includes(current.role);
  }

  /** Pocetna stranica posle prijave: kontrolor -> kontrole, ostali -> karte */
  homeRoute(): string {
    return this.hasRole('kontrolor') ? '/controls' : '/tickets';
  }

  private save(res: LoginResponse): void {
    localStorage.setItem(TOKEN_KEY, res.token);
    localStorage.setItem(USER_KEY, JSON.stringify(res.user));
    this.user.set(res.user);
  }

  private readStoredUser(): User | null {
    try {
      const raw = localStorage.getItem(USER_KEY);
      return raw && localStorage.getItem(TOKEN_KEY) ? (JSON.parse(raw) as User) : null;
    } catch {
      return null;
    }
  }
}
