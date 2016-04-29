export interface SelectableOption {
  id: string;
  title: string;
}

export interface Track extends SelectableOption {
  id: string;
  title: string;
  artist?: string;
  album?: string;
}

export interface Playlist extends SelectableOption {
  id: string;
  title: string;
  tracklist?: Track[];
  tracks?: number;
  image?: string;
}

export interface User {
  ldap: string;
  firstName: string;
  lastName: string;
  image?: string;
  paired: boolean;
}

export interface Playback {
  shuffle: boolean;
}

