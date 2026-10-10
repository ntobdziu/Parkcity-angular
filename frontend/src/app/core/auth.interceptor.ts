import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { API_URL } from './api.config';
import { AuthService } from './auth.service';

/**
 * Presrece svaki HTTP zahtev ka API-ju:
 *  - dodaje zaglavlje  Authorization: Bearer <token>
 *  - ako server vrati 401 (token nevazeci), odjavljuje korisnika i vraca ga na login
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  const token = auth.token;
  const request =
    token && req.url.startsWith(API_URL)
      ? req.clone({ setHeaders: { Authorization: `Bearer ${token}`, Accept: 'application/json' } })
      : req;

  return next(request).pipe(
    catchError((err: HttpErrorResponse) => {
      if (err.status === 401 && !req.url.endsWith('/login')) {
        auth.clear();
        router.navigateByUrl('/login');
      }
      return throwError(() => err);
    })
  );
};
