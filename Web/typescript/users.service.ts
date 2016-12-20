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

@Injectable()
export class UsersService {
  public errors: EventEmitter<any> = new EventEmitter();

  private _polling: boolean = false;
  private _maxPollInterval: number = 500; // milliseconds

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
   * Fetches the RFID mappings, including time of last tap.
   */
  private pollForMappings() {
    var startTime = new Date().getTime();

    this.fetchMappings().subscribe(res => {
      if (!this._polling) {
        return;
      }

      this.processMappings(res.rfidMappings);

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
   * Notifies subscribers of changes to RFID mappings.
   */
  private processMappings(mappings: API.RFIDMapping[]) {
    console.info('Received RFID mappings:', mappings);

    // TODO
  }

  /**
   * Fetches RFID->LDAP mappings along with their last tap timestamp.
   */
  private fetchMappings(): Rx.Observable<API.Response> {
    return this
      .makeGetRequest('/api/mappings/all')
      .map(res => res.json());
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

