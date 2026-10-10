import { Component, input, output } from '@angular/core';

/** Jednostavna paginacija: <app-pager [page]="1" [lastPage]="5" (pageChange)="load($event)" /> */
@Component({
  selector: 'app-pager',
  standalone: true,
  template: `
    @if (lastPage() > 1) {
      <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
          <li class="page-item" [class.disabled]="page() <= 1">
            <button type="button" class="page-link" (click)="go(page() - 1)">&laquo; Prethodna</button>
          </li>
          <li class="page-item disabled">
            <span class="page-link">{{ page() }} / {{ lastPage() }}</span>
          </li>
          <li class="page-item" [class.disabled]="page() >= lastPage()">
            <button type="button" class="page-link" (click)="go(page() + 1)">Sledeća &raquo;</button>
          </li>
        </ul>
      </nav>
    }
  `,
})
export class PagerComponent {
  page = input.required<number>();
  lastPage = input.required<number>();
  pageChange = output<number>();

  go(target: number): void {
    if (target >= 1 && target <= this.lastPage()) {
      this.pageChange.emit(target);
    }
  }
}
