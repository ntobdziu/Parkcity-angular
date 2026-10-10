import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';
import { Role } from './models';

/** Samo prijavljeni korisnici */
export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  return auth.isLoggedIn() ? true : router.createUrlTree(['/login']);
};

/** Login/registracija: ako je vec prijavljen, salji ga na pocetnu */
export const guestGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  return auth.isLoggedIn() ? router.createUrlTree([auth.homeRoute()]) : true;
};

/** Samo odredjene uloge: canActivate: [roleGuard('kontrolor', 'admin')] */
export const roleGuard = (...roles: Role[]): CanActivateFn => {
  return () => {
    const auth = inject(AuthService);
    const router = inject(Router);

    return auth.hasRole(...roles) ? true : router.createUrlTree([auth.homeRoute()]);
  };
};
