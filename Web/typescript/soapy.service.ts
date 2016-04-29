import {EventEmitter, Injectable} from 'angular2/core';
import {Http, Response, URLSearchParams, Headers, RequestOptions} from 'angular2/http';
import * as Rx from 'rxjs/Rx';

import {StaticData} from './StaticData';
import {BaseError} from './error';
import {User, Playlist, Playback} from './soapy.interfaces';
import * as API from './soapy.api.interfaces';


export interface ServiceAppData {
  playlists?: Playlist[];
  selectedPlaylist?: Playlist;
  user?: User;
}

export class APIError extends BaseError {
  constructor(message: string) {
    super(message);
  };
}

@Injectable()
export class SoapyService {
  public playlistsData: Rx.Observable<ServiceAppData> = null;
  public userData: Rx.Observable<User> = null;
  public errors: EventEmitter<any> = new EventEmitter();

  private playlists: { [id: string] : Playlist; } = {};

  constructor(private http: Http) {
    this.errors.subscribe((error) => {
      console.error('An error occurred in SoapyService:', error);
    });

    // API-formatted data embedded in the page.
    var rawUserData = Rx.Observable.of(StaticData.userData);

    // API-formatted data with a playlist list fetched with AJAX.
    var rawPlaylistsData = this.http.get('/api/me/playlists')
      .map(res => res.json());

    // Consolidated stream of all data sources, formatted as ServiceAppData.
    var appData = Rx.Observable.merge(rawPlaylistsData, rawUserData)
      .map(this.processAppData.bind(this))
      .catch(this.catchAPIErrors.bind(this))
      .publishReplay(1);

    // Stream of AppData for external consumers.
    this.playlistsData = appData.share();

    // Stream of User data for external consumers.
    this.userData = appData
      .filter((data: ServiceAppData) => {
        return data.user !== undefined;
      })
      .map((data: ServiceAppData) => {
        return data.user;
      })
      .share();

    // Kick off data loading, even if we have no other subscribers.
    appData.connect();
  }

  /**
   * Gets the playlist for a given id, or null if playlist isn't loaded.
   */
  public getPlaylist(id: string): Playlist {
    if (this.playlists[id]) {
      return this.playlists[id];
    }

    return null;
  }

  /**
   * Unpairs the associated Spotify account.
   */
  public unpair(): Rx.Observable<Response> {
    return this
      .makePostRequest('/api/me/unpair')
      .map(res => res.json());
  }

  /**
   * Selects a different playlist.
   */
  public selectPlaylist(playlist: Playlist): Rx.Observable<Response> {
    var params = new URLSearchParams();
    params.set('selectedPlaylistId', playlist.id);

    return this
      .makePostRequest('/api/me/playback', params)
      .map(res => res.json());
  }

  /**
   * Updates playback settings.
   */
  public updatePlayback(playback: Playback): Rx.Observable<Response> {
    var params = new URLSearchParams();
    params.set('shuffle', '' + playback.shuffle);

    return this
      .makePostRequest('/api/me/playback', params)
      .map(res => res.json());
  }

  /**
   * Formats API response to a ServiceAppData and caches data.
   *
   * Throws an error if the response is an error message.
   */
  private processAppData(data: API.Response): ServiceAppData {
    if (data.error) {
      throw new Error('API Error: ' + data.error);
    } else if (!data.user) {
      throw new Error('No User object in API response.');
    }

    var ret: ServiceAppData = {};

    ret.user = {
      ldap: data.user.ldap,
      firstName: data.user.firstName,
      lastName: data.user.lastName,
      paired: false,
    };

    if (data.user.spotifyAccount) {
      ret.user.image = data.user.spotifyAccount.avatar;
      ret.user.paired = true;
    }

    if (data.user.playlists) {
      data.user.playlists.forEach(this.cachePlaylist.bind(this));
      ret.playlists = data.user.playlists.map((apiPlaylist) => {
        return this.getPlaylist('' + apiPlaylist.soapyPlaylistId);
      });
    }

    if (data.user.selectedPlaylist) {
      var playlist = data.user.selectedPlaylist;

      if (!this.getPlaylist('' + playlist.soapyPlaylistId)) {
        this.cachePlaylist(playlist);
      }

      ret.selectedPlaylist = this.getPlaylist('' + playlist.soapyPlaylistId);
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

        playlist.image = images[images.length > 1 ? 1 : 0].url;
      }
    }

    return playlist;
  }

  /**
   * Adds the playlist to the playlist dictionary.
   */
  private cachePlaylist(playlist: API.SoapyPlaylist) {
    this.playlists['' + playlist.soapyPlaylistId] =
        this.formatPlaylistFromAPI(playlist);
  }

  /**
   * Makes a parameterize post request to the API.
   */
  private makePostRequest(route: string, params?: URLSearchParams)
      : Rx.Observable<Response> {

    var headers = new Headers({
      'Content-Type': 'application/x-www-form-urlencoded',
    });

    var options = new RequestOptions({
      headers: headers,
    });

    var body: string = params ? params.toString() : '';

    var res = this.http.post(route, body, options)
      .catch(this.catchAPIErrors.bind(this))
      .publishReplay(1);

    res.connect();

    return res;
  }

  /**
   * Catches Response errors and extracts an API error message if applicable.
   */
  private catchAPIErrors(err) {
    var ret = err;

    if (err instanceof Response) {
      var res: Response = err;
      var json = res.json();
      if (json.hasOwnProperty('error')) {
        ret = new APIError(json.error);
      }
    }

    this.errors.emit(ret);
    return Rx.Observable.throw(ret);
  }
}

