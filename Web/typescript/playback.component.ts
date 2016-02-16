import {Component, OnInit} from 'angular2/core';

import {Playlist} from './soapy.interfaces';
import {DropdownComponent} from './dropdown.component';
import {SoapyService, ServicePlaylistData} from './soapy.service';
import * as util from './soapy.utils';


@Component({
  providers: [SoapyService],
  directives: [DropdownComponent],
  selector: 'playback-component',
  templateUrl: '/app/playback.component.html',
})
export class PlaybackComponent implements OnInit {
  public playlists: Playlist[];
  public selectedPlaylist: Playlist;

  constructor(private _soapyService: SoapyService) {}

  public ngOnInit() {

    this._soapyService.getPlaylists()
    .then((data: ServicePlaylistData) => {
      this.playlists = data.playlists;
      this.selectedPlaylist = util.findByProperty(
          this.playlists, 'id', data.selectedPlaylist);
    });
  }
}

