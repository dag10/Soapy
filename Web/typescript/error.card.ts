import {Component, Input} from 'angular2/core';


@Component({
  selector: 'error-card',
  templateUrl: '/app/error.card.html',
  host: {
    '[class.hidden]': 'errors.length == 0',
    '[class.single-error]': 'errors.length == 1',
  },
})
export class ErrorCardComponent {
  @Input() errors: string[];
}

