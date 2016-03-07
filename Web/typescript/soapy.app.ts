import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {ErrorCardComponent} from './error.card';
import {AboutCardComponent} from './about.card';
import {PlaylistCardComponent} from './playlist.card';

import {Playlist} from './soapy.interfaces';
import {SoapyService, ServiceAppData} from './soapy.service';


@Component({
  providers: [SoapyService],
  selector: 'soapy-app',
  templateUrl: '/app/soapy.app.html',
  directives: [
    ErrorCardComponent,
    AboutCardComponent,
    PlaylistCardComponent,
  ],
})
export class SoapyAppComponent implements OnInit {
  public errors: string[] = [];
  public playlists: Playlist[] = null;
  public selectedPlaylist: Playlist = null;

  constructor(private _soapyService: SoapyService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._soapyService.errors.subscribe((err: any) => {
      var message = err.hasOwnProperty('message') ? err.message : '' + err;
      this.errors.push(message);
      this._changeDetector.detectChanges();
    });

    this._soapyService.playlistsData.subscribe((data: ServiceAppData) => {
      this.playlists = data.playlists;
      this.selectedPlaylist = data.selectedPlaylist;

      // Manually detect and propagate changes because:
      // http://stackoverflow.com/a/35106069/3333841
      this._changeDetector.detectChanges();

    });
  }
}

