export interface Playback {
  playbackMode: string;
}

export interface SpotifyAccount {
  username: string;
  accessToken?: string;
  avatar?: string;
}

export interface SpotifyPlaylist {
  id: string;
  href: string;
  uri: string;
  name: string;
  public: boolean;
  // Note: incomplete...
}

export interface SoapyPlaylist {
  soapyPlaylistId: number;
  spotifyPlaylistUri?: string;
  lastPlayedSongUri?: string;
  spotifyPlaylist?: SpotifyPlaylist;
}

export interface User {
  ldap: string;
  firstName: string;
  lastName: string;
  playback?: Playback;
  spotifyAccount?: SpotifyAccount;
  playlists?: SoapyPlaylist[];
  selectedPlaylist?: SoapyPlaylist;
}

export interface Response {
  user?: User;
  error?: string;
}

