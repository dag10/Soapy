import {EventEmitter, Injectable} from 'angular2/core';
import {Http, Response, URLSearchParams, Headers, RequestOptions} from 'angular2/http';
import * as Rx from 'rxjs/Rx';

import {StaticData} from './StaticData';
import {BaseError} from './error';
import {User, Playlist, Track, Playback} from './soapy.interfaces';
import * as API from './soapy.api.interfaces';


export interface ServiceAppData {
  playlist?: Playlist;
  playlists?: Playlist[];
  selectedPlaylist?: Playlist;
  user?: User;
  playback?: Playback;
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
  public playbackData: Rx.Observable<Playback> = null;
  public errors: EventEmitter<any> = new EventEmitter();

  private playlists: { [id: string] : Playlist; } = {};
  private _appData: EventEmitter<ServiceAppData> = new EventEmitter();
  private _selectedPlaylistId: string = null;

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
    var appData = this._appData.publishReplay(1);

    // Initial source of data: partial static data and loaded playlists data.
    Rx.Observable.merge(rawPlaylistsData, rawUserData)
      .map(this.processAppData.bind(this))
      .catch(this.catchAPIErrors.bind(this))
      .subscribe(data => {
        this._appData.emit(data);
      });

    // Stream of AppData for external consumers.
    this.playlistsData = appData.share();

    // Keep track of selected playlist id.
    this.playlistsData.subscribe((data: ServiceAppData) => {
      if (data.selectedPlaylist) {
        this._selectedPlaylistId = data.selectedPlaylist.id;
      }
    });

    // Stream of User data for external consumers.
    this.userData = appData
      .filter((data: ServiceAppData) => {
        return data.user !== undefined;
      })
      .map((data: ServiceAppData) => {
        return data.user;
      })
      .share();

    // Stream of playback settings data for external consumers.
    this.playbackData = appData
      .filter((data: ServiceAppData) => {
        return data.playback !== undefined;
      })
      .map((data: ServiceAppData) => {
        return data.playback;
      })
      .share();

    // Kick off data loading, even if we have no other subscribers.
    appData.connect();

    // If we already know the user has a selected playlist, start loading it.
    if (StaticData.userData
        && StaticData.userData.user
        && StaticData.userData.user.selectedPlaylistId) {
      this.fetchPlaylistWithTracklist(
        '' + StaticData.userData.user.selectedPlaylistId)
        .publish().connect();
    }
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
   * Gets data for a playlist including its tracklist.
   */
  public fetchPlaylistWithTracklist(id: string): Rx.Observable<Playlist> {
    var playlist = this.getPlaylist(id);
    if (playlist && playlist.tracklist) {
      return Rx.Observable.of(playlist);
    }

    return this
      .makeGetRequest(`/api/me/playlist/${ id }`)
      .map(res => res.json())
      .map(this.processAppData.bind(this))
      .map((data: ServiceAppData) => {
        if (data.playlist) {
          if (data.playlist.id === this._selectedPlaylistId) {
            this._appData.emit({
              selectedPlaylist: data.playlist,
            });
          }
          return data.playlist;
        } else {
          throw new Error('Failed to retreive track list.');
        }
      })
      .catch(this.catchAPIErrors.bind(this));
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

    if (data.playlist) {
      this.cachePlaylist(data.playlist);
      ret.playlist = this.getPlaylist('' + data.playlist.soapyPlaylistId);
    }

    if (data.user.selectedPlaylist) {
      var playlist = data.user.selectedPlaylist;

      if (!this.getPlaylist('' + playlist.soapyPlaylistId)) {
        this.cachePlaylist(playlist);
      }

      ret.selectedPlaylist = this.getPlaylist('' + playlist.soapyPlaylistId);
    }

    if (data.user.playback) {
      ret.playback = {
        shuffle: (data.user.playback.playbackMode === 'SHUFFLE'),
      };
    }

    return ret;
  }

  /**
   * Maps SpotifyTrack to Track object.
   */
  private formatTrackFromSpotifyTrack(track: API.SpotifyTrack): Track {
    var ret: Track = {
      id: track.uri,
      title: track.name,
    };

    if (track.artists) {
      ret.artists = track.artists.map(artist => artist.name);
    }

    return ret;
  }

  /**
   * Maps SoapyPlaylist object from an API response into a Playlist object.
   */
  private formatPlaylistFromAPI(data: API.SoapyPlaylist): Playlist {
    var playlist: Playlist = {
      id: '' + data.soapyPlaylistId,
      title: `Playlist ${ data.soapyPlaylistId }`,
    };

    if (data.spotifyPlaylist) {
      playlist.title = data.spotifyPlaylist.name;

      if (data.spotifyPlaylist.tracks) {
        playlist.tracks = data.spotifyPlaylist.tracks.total;
      }

      if (data.spotifyPlaylist.images) {
        var images = data.spotifyPlaylist.images;
        if (images.length > 0) {
          images.sort((a, b) => {
            return a.width - b.width;
          });

          playlist.image = images[images.length > 1 ? 1 : 0].url;
        }
      }
    }

    if (data.tracklist) {
      playlist.tracklist = data.tracklist.map(
        this.formatTrackFromSpotifyTrack);
    }

    return playlist;
  }

  /**
   * Adds the playlist to the playlist dictionary.
   *
   * It currently just crudly augments the playlist based on the assumption
   * that we fall under one of three different cases:
   *
   * Case 1: There is no cached playlist, so we cache the entire playlist
   *         we're given.
   * 
   * Case 2: The given playlist has a tracklist, so we add the tracklist to
   *         the cached playlist.
   *
   * Case 3: The cached playlist has a tracklist and ours doesn't, so we
   *         set the cached playlist to the given playlist but retain the
   *         tracklist.
   */
  private cachePlaylist(playlist: API.SoapyPlaylist) {
    var formattedPlaylist = this.formatPlaylistFromAPI(playlist);
    var idStr = '' + playlist.soapyPlaylistId;

    if (!this.playlists[idStr]) {
      this.playlists[idStr] = formattedPlaylist;
    } else if (formattedPlaylist.tracklist) {
      this.playlists[idStr].tracklist = formattedPlaylist.tracklist;
    } else {
      var tracklist = this.playlists[idStr].tracklist;
      this.playlists[idStr] = formattedPlaylist;
      this.playlists[idStr].tracklist = tracklist;
    }
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
   * Makes a get request to the API.
   */
  private makeGetRequest(route: string): Rx.Observable<Response> {
    var res = this.http.get(route)
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

