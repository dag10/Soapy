import {
  ViewChildren,
  QueryList,
  Component,
  OnInit,
  ChangeDetectorRef } from 'angular2/core';

import {ErrorCardComponent} from './error.card';
import {UserCardComponent} from './user.card';
import {RfidCardComponent} from './rfid.card';
import {SnackbarComponent} from './snackbar';

import {StaticData} from './StaticData';
import {RFID, User, UsersService} from './users.service';
import {SnackbarService} from './snackbar.service';

@Component({
  providers: [
    UsersService,
    SnackbarService,
  ],
  selector: 'soapy-app',
  template: StaticData.templates.UsersApp,
  directives: [
    ErrorCardComponent,
    UserCardComponent,
    RfidCardComponent,
    SnackbarComponent,
  ],
})
export class UsersAppComponent implements OnInit {
  @ViewChildren(RfidCardComponent) rfidCards: QueryList<RfidCardComponent>;

  public errors: string[] = [];

  private _showingAllUsers: boolean = false;

  constructor(private _usersService: UsersService,
              private _snackbarService: SnackbarService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._usersService.errors.subscribe((err: any) => {
      var message = err.hasOwnProperty('message') ? err.message : '' + err;
      this.errors.push(message);
      this._changeDetector.detectChanges();
      window.scrollTo(0, 0);
    });

    this._usersService.unknownRFIDsChanged.subscribe(() => {
      this._changeDetector.detectChanges();
    });

    this._usersService.usersChanged.subscribe(() => {
      this._changeDetector.detectChanges();
    });

    this._usersService.subscribeToUsers();
  }

  public get unknownRFIDs(): RFID[] {
    return this._usersService.unknownRFIDs;
  }

  /**
   * All users, sorted to show those with Spotify accounts first.
   */
  public get users(): User[] {
    return this._usersService.users.sort((user: User) => {
      return user.hasSpotifyAccount ? -1 : 1;
    });
  }

  /**
   * Either all users or just users with Spotify accounts.
   */
  public get usersToDisplay(): User[] {
    if (this.hideNonSpotifyUsers) {
      return this.users.filter((user: User) => {
        return user.hasSpotifyAccount;
      });
    } else {
      return this.users;
    }
  }

  public get suggestedUsers(): User[] {
    return this._usersService.suggestedUsers.slice(0, 4);
  }

  public unpairRFID(rfid: string) {
    var user = this._usersService.getUserForRFID(rfid);
    this._snackbarService.showUndo(
        rfid + ' has been unpaired from ' + user.firstName + '.',
        () => {
      this._usersService.pairRFID(rfid, user.ldap);
    });

    this._usersService.unpairRFID(rfid);
  }

  public pairRFID(rfid: string, ldap: string) {
    var user = this._usersService.getUserForLDAP(ldap);
    this._snackbarService.showUndo(
        rfid + ' has been paired with ' + user.firstName + ' ' +
        user.lastName + '.',
        () => {
      this._usersService.unpairRFID(rfid);
    });

    this._usersService.pairRFID(rfid, ldap);
  }

  public collapseOtherRfidCards(except: RfidCardComponent) {
    this.rfidCards.forEach((card: RfidCardComponent) => {
      if (card === except) {
        return;
      }

      card.collapseUsersList();
    });
  }

  public get hideNonSpotifyUsers(): boolean {
    return !this._showingAllUsers;
  }

  public showAllUsers() {
    this._showingAllUsers = true;
  }
}

