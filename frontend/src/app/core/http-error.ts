import { HttpErrorResponse } from '@angular/common/http';

/** Izvlaci poruku greske iz Laravel odgovora (422 validacija, 403, 404...). */
export function errorMessage(err: unknown, fallback = 'Došlo je do greške. Pokušajte ponovo.'): string {
  if (err instanceof HttpErrorResponse) {
    if (err.status === 0) {
      return 'Server nije dostupan. Proveri da li je backend pokrenut (php artisan serve).';
    }

    const body = err.error;

    if (body && body.errors) {
      const first = Object.values(body.errors as Record<string, string[]>)[0];
      if (first && first.length > 0) {
        return first[0];
      }
    }

    if (body && typeof body.message === 'string' && body.message.length > 0) {
      return body.message;
    }
  }

  return fallback;
}
