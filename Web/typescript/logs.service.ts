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

export interface Bathroom {
  name: string;
  lastLogId: number;
  logStream?: Rx.Observable<API.LogEvent[]>;
}

@Injectable()
export class LogsService {
  public errors: EventEmitter<any> = new EventEmitter();

  private _bathrooms: { [name: string]: Bathroom; } = {};
  private _allLogEvents: EventEmitter<API.LogEvent[]> =
    new EventEmitter<API.LogEvent[]>();
  private _currentlyFetchingRoom: Bathroom = null;
  private _maxPollInterval: number = 500; // milliseconds

  constructor(private http: Http) {
    this.errors.subscribe((error) => {
      console.error('An error occurred in LogService:', error);
    });

    this.getBathroomNames().forEach((name: string) => {
      var stream = this._allLogEvents
        .map(events => events.filter(
            event => event.Bathroom.toLowerCase() === name.toLowerCase()))
        .filter(events => events.length > 0)
        .publishReplay();

      stream.connect();

      this._bathrooms[name] = {
        name: name,
        lastLogId: 0,
        logStream: stream
      };

      stream.subscribe(events => {
        var greatestLogId = events[events.length - 1].Id;
        if (greatestLogId > this._bathrooms[name].lastLogId) {
          this._bathrooms[name].lastLogId = greatestLogId;
        }
      });
    });
  }

  /**
   * Repeatedly polls for latest logs for a particular room.
   */
  public subscribeToLog(room: string) {
    if (this._currentlyFetchingRoom === this._bathrooms[room]) {
      return;
    }

    var shouldFetch = (this._currentlyFetchingRoom === null);
    this._currentlyFetchingRoom = this._bathrooms[room];
    if (shouldFetch) {
      this.fetchLatestLogs();
    }
  }

  /**
   * Stops polling for logs.
   */
  public unsubscribeFromAllLogs() {
    this._currentlyFetchingRoom = null;
  }

  /**
   * Gets the names of the bathrooms that have logged data.
   */
  public getBathroomNames(): string[] {
    return StaticData.bathrooms || [];
  }

  /**
   * Gets the event stream for a given bathroom name.
   */
  public eventsForBathroom(room: string): Rx.Observable<API.LogEvent[]> {
    return this._bathrooms[room].logStream;
  }

  /**
   * Fetches the latest logs for the currently subscribed room, and
   * calls itself again once the data loads.
   */
  private fetchLatestLogs() {
    var room = this._currentlyFetchingRoom;
    if (room === null) {
      return;
    }

    var startTime = new Date().getTime();

    var rpc: Rx.Observable<API.Response>;
    if (room.lastLogId === 0) {
      rpc = this.fetchLatestLogsForRoom(room.name);
    } else {
      rpc = this.fetchLogsForRoomSince(room.name, room.lastLogId);
    }

    rpc.subscribe(res => {
      // If we are no longer subscribed to this room once the data comes in,
      // discard it.
      if (room !== this._currentlyFetchingRoom) {
        return;
      }

      this.processEvents(res.events);

      // Calculate a short delay for refetching to avoid spamming.
      var elapsed = new Date().getTime() - startTime;
      var timeout = this._maxPollInterval - elapsed;
      if (timeout < 0) {
        timeout = 0;
      }

      setTimeout(this.fetchLatestLogs.bind(this), timeout);
    });
  }

  /**
   * Notifies subscribers of the new events in order of ascending log ID.
   *
   * NOTE: Has the side effect of sorting the supplied array.
   */
  private processEvents(events: API.LogEvent[]) {
    events.sort((a, b) => (a.Id - b.Id));
    this._allLogEvents.emit(events);
  }

  /**
   * Fetches latest logs for a room name.
   */
  private fetchLatestLogsForRoom(room: string): Rx.Observable<API.Response> {
    return this
      .makeGetRequest(`/api/log/view/${ room }`)
      .map(res => res.json());
  }

  /**
   * Fetches logs for a room name after a certain log ID.
   */
  private fetchLogsForRoomSince(
      room: string, lastLogId: number): Rx.Observable<API.Response> {
    return this
      .makeGetRequest(`/api/log/view/${ room }/since/${ lastLogId }`)
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
    var ret = err;

    if (err instanceof Response) {
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

