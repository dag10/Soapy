import {Component} from 'angular2/core';

import {AboutCardComponent} from './about.card';
import {PlaylistCardComponent} from './playlist.card';


@Component({
  selector: 'soapy-app',
  templateUrl: '/app/soapy.app.html',
  directives: [
    AboutCardComponent,
    PlaylistCardComponent,
  ],
})
export class SoapyAppComponent {
}

