import {EventEmitter, Injectable} from 'angular2/core';
import {Http, Response} from 'angular2/http';
import * as Rx from 'rxjs/Rx';

import {StaticData} from './StaticData';
import {BaseError} from './error';
import * as API from './soapy.api.interfaces';


export class APIError extends BaseError {
  constructor(message: string) {
    super(message);
  };
}

export interface RFID extends API.RFID {}

export interface User {
  ldap: string;
  firstName: string;
  lastName: string;
  avatar?: string;
  lastTap?: string;
  rfids: RFID[];
}

@Injectable()
export class UsersService {
  public errors: EventEmitter<any> = new EventEmitter();

  private _polling: boolean = false;
  private _maxPollInterval: number = 500; // milliseconds

  private _unknownRFIDs: RFID[] = null;
  private _users: User[] = null;

  constructor(private http: Http) {
    this.errors.subscribe((error) => {
      console.error('An error occurred in UsersService:', error);
    });
  }

  /**
   * Repeatedly polls for changes in RFID taps or mappings.
   */
  public subscribeToUsers() {
    this._polling = true;
    this.pollForMappings();
  }

  /**
   * Stops polling.
   */
  public unsubscribeFromUsers() {
    this._polling = false;
  }

  /**
   * Get processed list of known users.
   */
  public get users(): User[] {
    if (this._users === null) {
      return [];
    }

    return this._users;
  }

  /**
   * Get processed list of unknown RFIDs.
   */
  public get unknownRFIDs(): RFID[] {
    if (this._unknownRFIDs === null) {
      return [];
    }

    return this._unknownRFIDs;
  }

  /**
   * Maps a user object from an API response into a user object for this page.
   */
  private mapApiUser(user: API.User): User {
    var ret: User = {
      ldap: user.ldap,
      firstName: user.firstName,
      lastName: user.lastName,
      rfids: [],
    };

    if (user.spotifyAccount) {
      ret.avatar = user.spotifyAccount.avatar;
    }

    if (user.rfids) {
      ret.rfids = <RFID[]>user.rfids.map(this.mapApiRFID.bind(this));
    }

    // TODO: Calculate last tap time

    return ret;
  }

  /**
   * Maps a API response's RFID tap into an RFID tap object for this page.
   */
  private mapApiRFID(rfid: API.RFID): RFID {
    return rfid;
  }

  /**
   * Fetches the RFID mappings, including time of last tap.
   */
  private pollForMappings() {
    var startTime = new Date().getTime();

    this.fetchMappings().subscribe(res => {
      if (!this._polling) {
        return;
      }

      this.processUnknownRFIDs(res.unknownRFIDs);
      this.processUsers(res.users);

      // Calculate a short delay for refetching to avoid spamming.
      var elapsed = new Date().getTime() - startTime;
      var timeout = this._maxPollInterval - elapsed;
      if (timeout < 0) {
        timeout = 0;
      }

      setTimeout(this.pollForMappings.bind(this), timeout);
    });
  }

  /**
   * Processes current list of uknown RFIDs.
   */
  private processUnknownRFIDs(rfids: API.RFID[]) {
    this._unknownRFIDs = <RFID[]>rfids.map(this.mapApiRFID.bind(this));

    console.info('Unknown RFID taps:', this._unknownRFIDs);
  }

  /**
   * Processes current list of known users.
   */
  private processUsers(users: API.User[]) {
    this._users = <User[]>users.map(this.mapApiUser.bind(this));

    console.info('Known users:', this._users);
  }

  /**
   * Fetches RFID->LDAP mappings along with their last tap timestamp.
   */
  private fetchMappings(): Rx.Observable<API.Response> {
    // TODO: TEMPORARY API MOCK
    var emitter = new EventEmitter<API.Response>();
    setTimeout(() => {
      emitter.emit({
        unknownRFIDs: [
          {
            rfid: 'UNKNOWN02',
            lastTap: '1482271152200',
          },
          {
            rfid: 'UNKNOWN01',
            lastTap: '' + new Date().getTime(),
          },
        ],
        users: [
          {
            ldap: 'dag10',
            firstName: 'Drew',
            lastName: 'Gottlieb',
            isAdmin: false,
            spotifyAccount: {
              username: 'spotifyDag10',
              avatar: 'http://placehold.it/250x250',
            },
            rfids: [
              {
                rfid: '12345',
                lastTap: '1482271039547',
              },
            ],
          },
          {
            ldap: 'dev',
            firstName: 'John',
            lastName: 'Smith',
            isAdmin: false,
            spotifyAccount: {
              username: 'spotifyDev',
              avatar: 'http://placehold.it/250x250',
            },
            rfids: [
              {
                rfid: 'devid01',
                lastTap: '1482271039547',
              },
              {
                rfid: 'devid02',
                lastTap: '1482271039547',
              },
            ],
          },
        ],
      });
    }, 200);
    return emitter;

    /*
    return this
      .makeGetRequest('/api/users/all')
      .map(res => res.json());
     */
  }

  /**
   * Makes a get request to the API.
   */
  private makeGetRequest(route: string): Rx.Observable<Response> {
    var res = this.http.get(route)
      .catch(this.catchAPIErrors.bind(this))
      .publishReplay(1);

    res.connect();

    return <Rx.ConnectableObservable<Response>> res;
  }

  /**
   * Catches Response errors and extracts an API error message if applicable.
   */
  private catchAPIErrors(err) {
    var ret: any = new Error('Error fetching data from server.');

    if (err.status !== 200) {
      ret = new Error(`Got status ${err.status} from server.`);
    } else if (err instanceof Response) {
      var res: Response = err;
      var json = res.json();
      if (json.hasOwnProperty('error')) {
        ret = new APIError(json.error);
      }
    }

    this.errors.emit(ret);
    return Rx.Observable.throw(ret);
  }
}

