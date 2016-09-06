import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {ErrorCardComponent} from './error.card';
import {LogsCardComponent} from './logs.card';

import {StaticData} from './StaticData';
import {LogsService} from './logs.service';


@Component({
  providers: [LogsService],
  selector: 'soapy-app',
  template: StaticData.templates.LogsApp,
  directives: [
    ErrorCardComponent,
    LogsCardComponent,
  ],
})
export class LogsAppComponent implements OnInit {
  public errors: string[] = [];

  constructor(private _logsService: LogsService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._logsService.errors.subscribe((err: any) => {
      var message = err.hasOwnProperty('message') ? err.message : '' + err;
      this.errors.push(message);
      this._changeDetector.detectChanges();
      window.scrollTo(0, 0);
    });
  }
}

