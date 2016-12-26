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
  host: {
    '[class.selecting-user]': '_selectingUser',
    '[class.no-suggested-users]': 'suggestedUsers.length === 0',
  },
})
export class RfidCardComponent implements AfterViewChecked {
  @Input() rfid: RFID;
  @Input() users: User[];
  @Input() suggestedUsers: User[];

  @Output() pair: EventEmitter<any> = new EventEmitter();

  private _id: string;
  private _selectingUser: boolean = false;

  constructor(private _el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this._id = 'rfid-' + Math.floor(Math.random() * 1000000);
  }

  public ngAfterViewChecked() {
    // Add material-design-lite javascript support to this element
    (<any>window).componentHandler.upgradeElements(this._el.nativeElement);
  }

  public get usernameId(): string {
    return this._id + '-username';
  }

  public get nonSuggestedUsers(): User[] {
    return this.users.filter(
      (user: User) => this.suggestedUsers.indexOf(user) < 0);
  }

  public get usersToShow(): User[] {
    var ret = this.suggestedUsers.slice(0);

    if (this._selectingUser) {
      this.nonSuggestedUsers.forEach((user: User) => {
        ret.push(user);
      });
    }

    return ret;
  }

  public pairWithUser(ldap: string) {
    this.pair.emit(ldap);
  }

  public expandUsersList() {
    this._selectingUser = true;
  }
}

