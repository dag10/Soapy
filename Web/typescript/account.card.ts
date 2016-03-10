///<reference path="smartcrop.d.ts"/>

import * as SmartCrop from 'smartcrop';
import {
  EventEmitter,
  Component,
  Input,
  Output,
  OnInit,
  ChangeDetectorRef} from 'angular2/core';

import {User, Playlist} from './soapy.interfaces';
import {SpinnerComponent} from './spinner';
import {StaticData} from './StaticData';


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
export class AccountCardComponent implements OnInit {
  @Input() playlists: Playlist[];
  @Output() unpair: EventEmitter<any> = new EventEmitter();

  public loadingSpotifyAuth: boolean = false;
  public spotifyAuthUrl: string = StaticData.spotifyAuthUrl;

  private _user: User;
  private imageLoaded: EventEmitter<any> = new EventEmitter();
  private imageScale: number = 1;
  private imagePosition: Position = { x: 0, y: 0 };

  constructor(private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    this.imageLoaded.subscribe((img) => {
      var options: SmartCrop.CropOptions = {
        minScale: 0.9,
        width: 115,
        height: 75,
      };

      SmartCrop.crop(img, options, (res: SmartCrop.CropResult) => {
        var crop = res.topCrop;
        this.imageScale = crop.width / img.width;
        this.imagePosition.x = crop.x / img.width;
        this.imagePosition.y = 1 - (crop.y / img.height);
        this._changeDetector.detectChanges();
      });
    });
  }

  @Input()
  public set user(user: User) {
    var oldImage = this.image;
    this._user = user;

    if (this.image && this.image !== oldImage) {
      var img = new Image();
      img.crossOrigin = "Anonymous";
      img.onload = () => {
        this.imageLoaded.next(img);
      };
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
      'background-size': (this.imageScale * 100) + '%',
      'background-position': (this.imagePosition.x * 100) + '% ' +
                             (this.imagePosition.y * 100) + '%',
    };
  }
}

