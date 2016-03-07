import {Component, Input} from 'angular2/core';


@Component({
  selector: 'error-card',
  template: (<any>window).templates.error_card,
  host: {
    '[class.hidden]': 'errors.length == 0',
    '[class.single-error]': 'errors.length == 1',
  },
})
export class ErrorCardComponent {
  @Input() errors: string[];
}

