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
  private _filter: string = null;

  constructor(private _el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this._id = 'rfid-' + Math.floor(Math.random() * 1000000);
  }

  public ngAfterViewChecked() {
    // Add material-design-lite javascript support to this element
    (<any>window).componentHandler.upgradeElements(this._el.nativeElement);
  }

  public get chipFilterId(): string {
    return this._id + '-chip-filter';
  }

  public get filterInputContainer(): HTMLElement {
    return this._el.nativeElement.querySelector('.mdl-textfield');
  }

  public get filterInput(): HTMLInputElement {
    return this._el.nativeElement.querySelector('input');
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

    if (this._filter !== null) {
      ret = ret.filter((user: User) => {
        var filter = this._filter.toLowerCase();
        var ldap = user.ldap.toLowerCase();
        var fullName = (user.firstName + ' ' + user.lastName).toLowerCase();

        return ldap.indexOf(filter) >= 0 || fullName.indexOf(filter) >= 0;
      });
    }

    return ret;
  }

  public get filterText(): string {
    return this._filter || '';
  }

  public setFilter(filter: string) {
    if (filter && filter.length > 0) {
      this._filter = filter;
    } else {
      this._filter = null;

      // Needed to get the Material placeholder element to be visible.
      this.filterInputContainer.classList.remove('is-dirty');
    }
  }

  public clearFilter() {
    this.setFilter(null);
  }

  public pairWithUser(ldap: string) {
    this.pair.emit(ldap);
  }

  public expandUsersList() {
    this._selectingUser = true;

    // Delay to allow template to re-render
    setTimeout(() => {
      this.filterInput.focus();
    }, 50);
  }

  public collapseUsersList() {
    this._selectingUser = false;
    this.clearFilter();
  }

  public filterKeyDown(event: any) {
    if (event.keyCode === 27 /* escape key */) {
      this.collapseUsersList();
    } else if (event.keyCode === 13 /* enter key */) {
      this.pairWithUser(this.usersToShow[0].ldap);
    }
  }
}

