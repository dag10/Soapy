import {Component, OnInit, ChangeDetectorRef} from 'angular2/core';

import {SpinnerComponent} from './spinner';
import {ErrorCardComponent} from './error.card';
import {AboutCardComponent} from './about.card';
import {AccountCardComponent} from './account.card';
import {PlaylistCardComponent} from './playlist.card';
import {PlaybackCardComponent} from './playback.card';

import {StaticData} from './StaticData';
import {User, Playlist} from './soapy.interfaces';
import {SoapyService, ServiceAppData} from './soapy.service';


@Component({
  providers: [SoapyService],
  selector: 'soapy-app',
  template: StaticData.templates.SoapyApp,
  directives: [
    SpinnerComponent,
    ErrorCardComponent,
    AboutCardComponent,
    AccountCardComponent,
    PlaylistCardComponent,
    PlaybackCardComponent,
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
      window.scrollTo(0, 0);
    });

    this._soapyService.userData.subscribe((user: User) => {
      this.user = user;
      this._changeDetector.detectChanges();
    });

    this._soapyService.playlistsData.subscribe((data: ServiceAppData) => {
      if (data.playlists === null) {
        this.playlists = null;
      } else if (data.playlists !== undefined) {
        this.playlists = data.playlists;
      }

      if (data.selectedPlaylist === null) {
        this.selectedPlaylist = null;
      } else if (data.selectedPlaylist !== undefined) {
        this.selectedPlaylist = data.selectedPlaylist;
      }

      this._changeDetector.detectChanges();
    });
  }

  public get initialDataIsLoaded(): boolean {
    if (this.user !== null) {
      if (!this.user.paired) {
        // No paired account, so no playlists will be loaded.
        return true;
      } else if (this.playlists !== null) {
        // There is a paired account and playlists are loaded.
        return true;
      }
    }

    return false;
  }

  public unpair() {
    this._soapyService
      .unpair()
      .subscribe((res) => {
        this.user.paired = false;
        this.playlists = null;
        this.selectedPlaylist = null;
      });
  }

  public selectPlaylist(playlist: Playlist) {
    var formerPlaylist = this.selectedPlaylist;
    this.selectedPlaylist = playlist;

    if (playlist !== null) {
      this._soapyService.selectPlaylist(playlist).subscribe((res) => {
        // nothing
      }, (err) => {
        this.selectedPlaylist = formerPlaylist;
      });
    }
  }
}

