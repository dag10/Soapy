import {
  Component,
  ElementRef,
  OnInit,
  AfterViewInit,
  ChangeDetectorRef} from 'angular2/core';

import {StaticData} from './StaticData';
import {LogsService} from './logs.service';
import {SpinnerComponent} from './spinner';
import * as API from './soapy.api.interfaces';
import * as Rx from 'rxjs/Rx';

declare var jQuery: JQueryStatic;

interface DisplayLogEvent extends API.LogEvent {
  DisplayTime?: string;
}


@Component({
  directives: [
    SpinnerComponent,
  ],
  selector: 'logs-card',
  template: StaticData.templates.LogsCard,
})
export class LogsCardComponent implements OnInit, AfterViewInit {
  private _currentBathroom: string = null;
  private _currentSubscription: Rx.Subscription = null;
  private _events: DisplayLogEvent[] = [];
  private _dontRerenderLoggings = false;
  private _isPaused: boolean = false;
  private $el: JQuery;

  constructor(private el: ElementRef,
              private _logsService: LogsService,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
  }

  public ngOnInit() {
    if (this.bathrooms.length > 0) {
      this.chooseBathroom(this.bathrooms[0]);
    }
  }

  public ngAfterViewInit() {
    this.$el.hide().fadeIn();
  }

  public get hasBathrooms(): boolean {
    return this.bathrooms.length > 0;
  }

  public get bathrooms(): string[] {
    return this._logsService.getBathroomNames();
  }

  public get currentBathroom(): string {
    return this._currentBathroom;
  }

  public chooseBathroom(bathroom: string) {
    if (this._currentBathroom === bathroom) {
      return;
    }

    this._currentBathroom = bathroom;

    this.removeCurrentSubscription();
    this._events = [];

    this._dontRerenderLoggings = true;
    this._currentSubscription = this._logsService
      .eventsForBathroom(bathroom)
      .subscribe(this.eventHandler.bind(this));
    this._logsService.subscribeToLog(bathroom);
    this._isPaused = false;

    this._changeDetector.detectChanges();
    this._dontRerenderLoggings = false;
  }

  public pause() {
    if (this._isPaused) {
      return;
    }

    this._logsService.unsubscribeFromAllLogs();
    this._isPaused = true;
  }

  public resume() {
    if (!this._isPaused) {
      return;
    }

    if (this._currentBathroom !== null) {
      this._logsService.subscribeToLog(this._currentBathroom);
    }
    this._isPaused = false;
  }

  public toggleRealtime() {
    if (this.isPaused) {
      this.resume();
    } else {
      this.pause();
    }
  }

  public eventHandler(events: API.LogEvent[]) {
    events.forEach(event => {
      var displayEvent = <DisplayLogEvent> event;
      var date = new Date(displayEvent.Time);
      displayEvent.DisplayTime = (date.getMonth() + 1) + "/" + date.getDate() +
        " " + date.getHours() + ":" + date.getMinutes() + ":" +
        date.getSeconds();
      this._events.unshift(displayEvent);
    });

    if (!this._dontRerenderLoggings) {
      this._changeDetector.detectChanges();
    }
  }

  public get events(): DisplayLogEvent[] {
    return this._events;
  }

  public get isPaused(): boolean {
    return this._isPaused;
  }

  public get showSpinner(): boolean {
    return this._events.length === 0;
  }

  private removeCurrentSubscription() {
    if (this._currentSubscription) {
      this._currentSubscription.unsubscribe();
      this._currentSubscription = null;
    }
  }
}

