import {Component, Input} from 'angular2/core';

import {Playlist} from './soapy.interfaces';


@Component({
  selector: 'playlist-card',
  templateUrl: '/app/playlist.card.html',
})
export class PlaylistCardComponent {
  @Input() playlists: Playlist[];
  @Input() selectedPlaylist: Playlist;
}

