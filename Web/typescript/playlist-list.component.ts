import {Component, OnInit} from 'angular2/core';

import {Playlist} from './soapy.interfaces';


@Component({
  selector: 'playlist-list',
  templateUrl: '/app/playlist-list.html',
})
export class PlaylistListComponent implements OnInit {
  //constructor(private _service: PlaylistService) {}

  public playlists: Playlist[];
  public selectedPlaylist: Playlist;

  public ngOnInit() {
    this.playlists = [
      {
        id: 1,
        title: 'The Happy Hipster',
      },
      {
        id: 2,
        title: 'Hot Alternative',
      },
      {
        id: 3,
        title: 'Workout',
      },
      {
        id: 4,
        title: 'Liked On Radio',
      },
    ];

    this.selectedPlaylist = this.playlists[1];
  }

  public selectPlaylist(playlist: Playlist) {
    this.selectedPlaylist = playlist;
  }
}

