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
  logStream?: Rx.Observable<API.LogEvent>;
}

@Injectable()
export class LogsService {
  public errors: EventEmitter<any> = new EventEmitter();

  private _bathrooms: { [name: string]: Bathroom; } = {};
  private _allLogEvents: EventEmitter<API.LogEvent> =
    new EventEmitter<API.LogEvent>();

  constructor(private http: Http) {
    this.errors.subscribe((error) => {
      console.error('An error occurred in LogService:', error);
    });

    this.getBathroomNames().forEach((name: string) => {
      var stream = this._allLogEvents
        .filter((event) => event.Bathroom === name)
        .publishReplay();

      stream.connect();

      this._bathrooms[name] = {
        name: name,
        lastLogId: 0,
        logStream: stream
      };

      stream.subscribe((event) => {
        this._bathrooms[name].lastLogId = event.Id;
      });
    });

    var nextId = 1;
    setInterval(() => {
      this.getBathroomNames().forEach((room) => {
        this._allLogEvents.emit({
          Id: nextId++,
          Bathroom: room,
          Time: new Date().toString(),
          Message: 'Room: ' + room + ', time: ' + (new Date().getTime())
        });
      });
    }, 1000);

    //console.info('Mocking events...');
    //this._allLogEvents.emit({
      //Id: 1,
      //Bathroom: 'dev',
      //Time: '0:0:0',
      //Message: 'FIRST!'
    //});

    //setTimeout(() => {
      //this._allLogEvents.emit({
        //Id: 2,
        //Bathroom: 'southside_mens',
        //Time: '0:0:0',
        //Message: 'DELAYED in WRONG BATHROOM!'
      //});
    //}, 2500);

    //setTimeout(() => {
      //this._allLogEvents.emit({
        //Id: 2,
        //Bathroom: 'dev',
        //Time: '0:0:0',
        //Message: 'DELAYED in CORRECT BATHROOM!'
      //});
    //}, 3000);
  }

  public subscribeToLog(room: string) {
  }

  public unsubscribeFromAllLogs() {
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
  public eventsForBathroom(room: string): Rx.Observable<API.LogEvent> {
    return this._bathrooms[room].logStream;
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

