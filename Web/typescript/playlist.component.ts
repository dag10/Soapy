import {Component} from 'angular2/core';

import {PlaylistListComponent} from './playlist-list.component';


@Component({
  directives: [PlaylistListComponent],
  selector: 'playlist-component',
  templateUrl: '/app/playlist.controls.html',
})
export class PlaylistComponent {
}

