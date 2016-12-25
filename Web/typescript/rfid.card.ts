import {
  EventEmitter,
  Component,
  Input,
  Output,
  ElementRef,
  AfterViewChecked,
  ChangeDetectorRef} from 'angular2/core';

import {User, RFID} from './users.service';
import {StaticData} from './StaticData';


@Component({
  selector: 'rfid-card',
  template: StaticData.templates.RfidCard,
})
export class RfidCardComponent implements AfterViewChecked {
  @Input() rfid: RFID;
  @Input() suggestedUsers: User[];

  @Output() pair: EventEmitter<any> = new EventEmitter();

  private _id: string;

  constructor(private _el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this._id = 'rfid-' + Math.floor(Math.random() * 1000000);
  }

  public ngAfterViewChecked() {
    // Add material-design-lite javascript support to this element
    (<any>window).componentHandler.upgradeElements(this._el.nativeElement);
  }

  public pairWithSuggestion(ldap: string) {
    this.pair.emit(ldap);
  }

  public get usernameId(): string {
    return this._id + '-username';
  }
}

