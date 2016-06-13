
import {
  EventEmitter,
  Component,
  Input,
  Output,
  ElementRef,
  AfterViewInit,
  ChangeDetectorRef} from 'angular2/core';

import {User, Playlist} from './soapy.interfaces';
import {SpinnerComponent} from './spinner';
import {StaticData} from './StaticData';

declare var jQuery: JQueryStatic;


interface Size {
  width: number;
  height: number;
}

interface Position {
  x: number;
  y: number;
}

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
export class AccountCardComponent implements AfterViewInit {
  @Input() playlists: Playlist[];
  @Output() unpair: EventEmitter<any> = new EventEmitter();

  public loadingSpotifyAuth: boolean = false;
  public spotifyAuthUrl: string = StaticData.spotifyAuthUrl;

  private $el: JQuery;
  private _user: User;

  constructor(private el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
  }

  public ngAfterViewInit() {
    this.$el.hide().fadeIn();
  }

  @Input()
  public set user(user: User) {
    var oldImage = this.image;
    this._user = user;

    if (this.image && this.image !== oldImage) {
      var img = new Image();
      img.crossOrigin = "Anonymous";
      img.src = this.image;
    }
  }

  public get user(): User {
    return this._user;
  }

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

  public get style(): Object {
    return {
      'background-image': 'url(\'' + this.image + '\')',
    };
  }
}

