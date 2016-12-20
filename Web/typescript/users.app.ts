import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {ErrorCardComponent} from './error.card';

import {StaticData} from './StaticData';
import {UsersService} from './users.service';


@Component({
  providers: [UsersService],
  selector: 'soapy-app',
  template: StaticData.templates.UsersApp,
  directives: [
    ErrorCardComponent,
  ],
})
export class UsersAppComponent implements OnInit {
  public errors: string[] = [];

  constructor(private _usersService: UsersService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._usersService.errors.subscribe((err: any) => {
      var message = err.hasOwnProperty('message') ? err.message : '' + err;
      this.errors.push(message);
      this._changeDetector.detectChanges();
      window.scrollTo(0, 0);
    });

    this._usersService.subscribeToUsers();
  }
}

