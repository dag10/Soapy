import * as API from './soapy.api.interfaces';

interface TemplateFormat {
  Spinner: string;
  SoapyApp: string;
  ErrorCard: string;
  AboutCard: string;
  AccountCard: string;
  PlaylistCard: string;
}

interface StaticDataFormat {
  spotifyAuthUrl: string;
  userData: API.Response;
  templates: TemplateFormat;
}

export var StaticData: StaticDataFormat;

