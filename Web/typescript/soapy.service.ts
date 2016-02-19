import {Injectable} from 'angular2/core';
import {Http} from 'angular2/http';
import 'rxjs/add/operator/map';
import * as Rx from 'rxjs';

import {Playlist} from './soapy.interfaces';
import * as API from './soapy.api.interfaces';


export interface ServicePlaylistData {
  playlists?: Playlist[];
  selectedPlaylist?: string;
}

@Injectable()
export class SoapyService {
  constructor(private http: Http) {}

  public getPlaylists(): Rx.Observable<ServicePlaylistData> {
    return this.http.get('/api/me/playlists')
      .map(res => res.json())
      .map(this.formatPlaylistsData.bind(this));
  }

  private formatPlaylistFromAPI(data: API.SoapyPlaylist): Playlist {
    return {
      id: '' + data.soapyPlaylistId,
      title: data.spotifyPlaylist.name,
    };
  }

  private formatPlaylistsData(data: API.Response): ServicePlaylistData {
    var ret: ServicePlaylistData = {
      playlists: data.user.playlists.map(this.formatPlaylistFromAPI),
    };

    if (data.user.selectedPlaylist) {
      ret.selectedPlaylist = '' + data.user.selectedPlaylist.soapyPlaylistId;
    }

    return ret;
  }
}

