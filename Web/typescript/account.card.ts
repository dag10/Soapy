import {EventEmitter, Component, Input, Output} from 'angular2/core';

import {User, Playlist} from './soapy.interfaces';
import {SpinnerComponent} from './spinner';
import {StaticData} from './StaticData';


@Component({
  directives: [
    SpinnerComponent,
  ],
  selector: 'account-card',
  template: StaticData.templates.AccountCard,
  host: {
    '[class.hidden]': '!user',
    '[class.paired]': 'user && user.paired',
  },
})
export class AccountCardComponent {
  @Input() user: User;
  @Input() playlists: Playlist[];
  @Output() unpair: EventEmitter<any> = new EventEmitter();

  public loadingSpotifyAuth: boolean = false;
  public spotifyAuthUrl: string = StaticData.spotifyAuthUrl;

  public get image(): string {
    if (this.user && this.user.image) {
      return this.user.image;
    }

    return null;
  }

  public get numPlaylists(): number {
    if (this.playlists) {
      return this.playlists.length;
    }

    return null;
  }

  public login() {
    this.loadingSpotifyAuth = true;
  }
}

