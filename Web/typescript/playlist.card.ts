import {Component, Input} from 'angular2/core';

import {Playlist} from './soapy.interfaces';


@Component({
  selector: 'playlist-card',
  template: (<any>window).templates.playlist_card,
  host: {
    '[class.hidden]': '!playlists',
  },
})
export class PlaylistCardComponent {
  @Input() playlists: Playlist[];
  @Input() selectedPlaylist: Playlist;
}

