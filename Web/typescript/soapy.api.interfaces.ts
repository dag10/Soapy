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

export interface User {
  ldap: string;
  firstName: string;
  lastName: string;
  playback?: Playback;
  spotifyAccount?: SpotifyAccount;
  playlists?: SoapyPlaylist[];
  selectedPlaylist?: SoapyPlaylist;
  selectedPlaylistId?: number;
}

export interface Response {
  user?: User;
  playlist?: SoapyPlaylist;
  error?: string;
}

