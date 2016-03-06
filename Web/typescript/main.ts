///<reference path="../node_modules/angular2/typings/browser.d.ts"/>

import {bootstrap} from 'angular2/platform/browser';
import {HTTP_PROVIDERS} from 'angular2/http';
import {SoapyAppComponent} from './soapy.app';

bootstrap(SoapyAppComponent, [HTTP_PROVIDERS]);

