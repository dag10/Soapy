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
  tracks?: Track[];
}

