import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {ErrorCardComponent} from './error.card';
import {AboutCardComponent} from './about.card';
import {AccountCardComponent} from './account.card';
import {PlaylistCardComponent} from './playlist.card';

import {User, Playlist} from './soapy.interfaces';
import {SoapyService, ServiceAppData} from './soapy.service';


@Component({
  providers: [SoapyService],
  selector: 'soapy-app',
  template: (<any>window).templates.soapy_app,
  directives: [
    ErrorCardComponent,
    AboutCardComponent,
    AccountCardComponent,
    PlaylistCardComponent,
  ],
})
export class SoapyAppComponent implements OnInit {
  public errors: string[] = [];
  public playlists: Playlist[] = null;
  public selectedPlaylist: Playlist = null;
  public user: User = null;

  constructor(private _soapyService: SoapyService,
              private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this._soapyService.errors.subscribe((err: any) => {
      var message = err.hasOwnProperty('message') ? err.message : '' + err;
      this.errors.push(message);
      this._changeDetector.detectChanges();
    });

    this._soapyService.userData.subscribe((user: User) => {
      this.user = user;
      this._changeDetector.detectChanges();
    });

    this._soapyService.playlistsData.subscribe((data: ServiceAppData) => {
      if (data.playlists) {
        this.playlists = data.playlists;
      }

      if (data.selectedPlaylist) {
        this.selectedPlaylist = data.selectedPlaylist;
      }

      this._changeDetector.detectChanges();
    });
  }

  public selectPlaylist(playlist: Playlist) {
    this.selectedPlaylist = playlist;
    this._soapyService.selectPlaylist(playlist);
  }
}

