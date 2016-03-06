import {Injectable} from 'angular2/core';
import {Http} from 'angular2/http';
import 'rxjs/add/operator/map';
import * as Rx from 'rxjs';

import {Playlist} from './soapy.interfaces';
import * as API from './soapy.api.interfaces';


export interface ServiceAppData {
  playlists?: Playlist[];
  selectedPlaylist?: string;
}

@Injectable()
export class SoapyService {
  private playlistResponseObservable: Rx.Observable<ServiceAppData> = null;

  constructor(private http: Http) {
    this.playlistResponseObservable = this.http.get('/api/me/playlists')
      .map(res => res.json())
      .map(this.formatAppData.bind(this));
  }

  /**
   * Gets the playlists from the server, or fails with an error.
   */
  public getPlaylists(): Rx.Observable<ServiceAppData> {
    return this.playlistResponseObservable;
  }

  /**
   * Maps API response to a ServiceAppData.
   *
   * Throws an error if the response is an error message.
   */
  private formatAppData(data: API.Response): ServiceAppData {
    if (data.error) {
      throw new Error('API Error: ' + data.error);
    } else if (!data.user) {
      throw new Error('No User object in API response.');
    }

    var ret: ServiceAppData = {};

    if (data.user.playlists) {
      ret.playlists = data.user.playlists.map(this.formatPlaylistFromAPI);
    }

    if (data.user.selectedPlaylist) {
      ret.selectedPlaylist = '' + data.user.selectedPlaylist.soapyPlaylistId;
    }

    return ret;
  }

  /**
   * Maps SoapyPlaylist object from an API response into a Playlist object.
   */
  private formatPlaylistFromAPI(data: API.SoapyPlaylist): Playlist {
    var playlist: Playlist = {
      id: '' + data.soapyPlaylistId,
      title: data.spotifyPlaylist.name,
      tracks: data.spotifyPlaylist.tracks.total,
    };

    if (data.spotifyPlaylist && data.spotifyPlaylist.images) {
      var images = data.spotifyPlaylist.images;
      if (images.length > 0) {
        images.sort((a, b) => {
          return a.width - b.width;
        });
        playlist.image = images[0].url;
      }
    }

    return playlist;
  }
}

