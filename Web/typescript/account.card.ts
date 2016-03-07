import {Component, Input} from 'angular2/core';

import {User, Playlist} from './soapy.interfaces';


@Component({
  selector: 'account-card',
  templateUrl: '/app/account.card.html',
  host: {
    '[class.hidden]': '!user',
    '[class.paired]': 'user && user.paired',
  },
})
export class AccountCardComponent {
  @Input() user: User;
  @Input() playlists: Playlist[];

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
}

