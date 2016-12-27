export interface Playback {
  playbackMode: string;
}

export interface SpotifyAccount {
  username: string;
  accessToken?: string;
  avatar?: string;
}

export interface SpotifyPlaylistImage {
  height: number;
  width: number;
  url: string;
}

export interface SpotifyPlaylistTracks {
  total: number;
}

export interface SpotifyTrackArtist {
  name: string;
}

export interface SpotifyTrack {
  duration_ms: number;
  name: string;
  uri: string;
  artists: SpotifyTrackArtist[];
  is_local: boolean;
  is_valid: boolean;
}

export interface SpotifyPlaylist {
  id: string;
  href: string;
  uri: string;
  name: string;
  public: boolean;
  tracks: SpotifyPlaylistTracks;
  images?: SpotifyPlaylistImage[];
}

export interface SoapyPlaylist {
  soapyPlaylistId: number;
  spotifyPlaylistUri?: string;
  lastPlayedSongUri?: string;
  spotifyPlaylist?: SpotifyPlaylist;
  tracklist?: SpotifyTrack[];
}

export interface RFID {
  rfid: string;
  lastTap?: number;
}

export interface User {
  ldap: string;
  firstName: string;
  lastName: string;
  isAdmin: boolean;
  playback?: Playback;
  spotifyAccount?: SpotifyAccount;
  playlists?: SoapyPlaylist[];
  selectedPlaylist?: SoapyPlaylist;
  selectedPlaylistId?: number;
  rfids?: RFID[];
}

export interface LogEvent {
  Id: number;
  Bathroom?: string;
  Level?: string;
  Time: string;
  Tag?: string;
  Message: string;
}

export interface Response {
  user?: User;
  users?: User[];
  playlist?: SoapyPlaylist;
  error?: string;
  events?: LogEvent[];
  unknownRFIDs?: RFID[];
}

