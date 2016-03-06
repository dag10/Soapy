import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {Playlist} from './soapy.interfaces';
import {SoapyService, ServiceAppData} from './soapy.service';
import * as util from './soapy.utils';


@Component({
  providers: [SoapyService],
  selector: 'playback-card',
  templateUrl: '/app/playback.component.html',
})
export class PlaybackComponent implements OnInit {
  public playlists: Playlist[] = null;
  public selectedPlaylist: Playlist = null;

  constructor(private _soapyService: SoapyService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._soapyService.getPlaylists()
    .subscribe((data: ServiceAppData) => {
      this.playlists = data.playlists;

      if (this.playlists) {
        this.selectedPlaylist = util.findByProperty(
            this.playlists, 'id', data.selectedPlaylist);
      }

      // Manually detect and propagate changes because:
      // http://stackoverflow.com/a/35106069/3333841
      this._changeDetector.detectChanges();

    }, (error) => {
      console.error("Failed to get playlists:", error);
    });
  }
}

