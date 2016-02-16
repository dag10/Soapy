import {Component, OnInit} from 'angular2/core';

import {Playlist} from './soapy.interfaces';
import {DropdownComponent} from './dropdown.component';


@Component({
  directives: [DropdownComponent],
  selector: 'playlist-component',
  templateUrl: '/app/playlist.controls.html',
})
export class PlaylistComponent implements OnInit {
  public playlists: Playlist[];
  public selectedPlaylist: Playlist;

  //constructor(private _service: PlaylistService) {}

  public ngOnInit() {

    // Temporary bootstrap with fake data
    this.playlists = [
      {
        id: 'playlist:1',
        title: 'The Happy Hipster',
        tracks: [
          {
            id: 'track:1',
            title: 'Hipster Track One',
            artist: 'The Mowgli\'s',
          },
          {
            id: 'track:2',
            title: 'Hipster Track Two',
            artist: 'Dirtwire',
          },
          {
            id: 'track:3',
            title: 'Hipster Track Three',
            artist: 'Powers',
          },
        ],
      },
      {
        id: 'playlist:2',
        title: 'Hot Alternative',
        tracks: [
          {
            id: 'track:4',
            title: 'Alt Track One',
            artist: 'Daft Punk',
          },
          {
            id: 'track:5',
            title: 'Alt Track Two',
            artist: 'Little Comets',
          },
          {
            id: 'track:6',
            title: 'Alt Track Three',
            artist: 'Kanye West',
          },
        ],
      },
      {
        id: 'playlist:3',
        title: 'Workout',
        tracks: [
          {
            id: 'track:7',
            title: 'Some Workout Track',
            artist: 'Loners',
          },
        ],
      },
      {
        id: 'playlist:4',
        title: 'Liked On Radio',
      },
    ];

    this.selectedPlaylist = this.playlists[1];
  }
}

