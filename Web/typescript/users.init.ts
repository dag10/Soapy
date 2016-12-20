///<reference path="../node_modules/angular2/typings/browser.d.ts"/>
///<reference path="../typings/main.d.ts"/>

import {bootstrap} from 'angular2/platform/browser';
import {HTTP_PROVIDERS} from 'angular2/http';
import {UsersAppComponent} from './users.app';

bootstrap(UsersAppComponent, [HTTP_PROVIDERS]);

