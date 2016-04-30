import {Component, Input} from 'angular2/core';
import {StaticData} from './StaticData';


@Component({
  selector: 'error-card',
  template: StaticData.templates.ErrorCard,
  host: {
    '[class.hidden]': 'errors.length == 0',
    '[class.single-error]': 'errors.length == 1',
  },
})
export class ErrorCardComponent {
  @Input() errors: string[];
}

