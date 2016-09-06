import * as API from './soapy.api.interfaces';

interface TemplateFormat {
  Spinner: string;
  SoapyApp: string;
  ErrorCard: string;
  AboutCard: string;
  AccountCard: string;
  PlaylistCard: string;
  PlaybackCard: string;
  LogsApp: string;
  LogsCard: string;
}

interface StaticDataFormat {
  spotifyAuthUrl: string;
  userData: API.Response;
  templates: TemplateFormat;
  bathrooms?: string[];
}

export var StaticData: StaticDataFormat;

