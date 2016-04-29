///<reference path="smartcrop.d.ts"/>

import * as SmartCrop from 'smartcrop';
import {
  EventEmitter,
  Component,
  Input,
  Output,
  OnInit,
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
export class AccountCardComponent implements OnInit, AfterViewInit {
  @Input() playlists: Playlist[];
  @Output() unpair: EventEmitter<any> = new EventEmitter();

  public loadingSpotifyAuth: boolean = false;
  public spotifyAuthUrl: string = StaticData.spotifyAuthUrl;

  private $el: JQuery;
  private _user: User;
  private imageLoaded: EventEmitter<any> = new EventEmitter();
  private imageSize: Size = { width: 115, height: 75 };
  private imagePosition: Position = { x: 0, y: 0 };

  constructor(private el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
  }

  public ngOnInit() {
    this.imageLoaded.subscribe((img) => {
      // Aim for the focus of the image being to the left of the start of
      // the slant, except extending about 1/3 into the slant.
      var minWidth = 65;
      var maxWidth = 115;
      var height = 75;

      var options: SmartCrop.CropOptions = {
        minScale: 0.7,
        width: minWidth,
        height: height,
      };

      SmartCrop.crop(img, options, (res: SmartCrop.CropResult) => {
        var crop = res.topCrop;

        // Stretch width from target 65px to final 115px.
        // Also stretch height proportionally.
        crop.width = crop.width * (maxWidth / minWidth);
        crop.height = crop.height * (maxWidth / minWidth);

        if (crop.width > img.width) {
          // If stretched width is too wide, scale width and height down.
          crop.height = crop.height * (img.width / crop.width);
          crop.width = img.width;
        }

        // If stretched width sticks off of right edge, shrink it a bit.
        if (crop.x + crop.width > img.width) {
          var recentSize = { width: crop.width, height: crop.height };

          crop.width = img.width - crop.x;
          crop.height = crop.height * (crop.width / recentSize.width);

          crop.y += ((recentSize.height - crop.height) / 2);
        }

        this.imageSize.width = maxWidth * (img.width / crop.width);
        this.imageSize.height = this.imageSize.width;

        this.imagePosition.x = crop.x * (this.imageSize.width / img.width);
        this.imagePosition.y = crop.y * (this.imageSize.height / img.height);

        this._changeDetector.detectChanges();
      });
    });
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
      'background-size': this.imageSize.width + 'px ' +
                         this.imageSize.height + 'px',
      'background-position': -this.imagePosition.x + 'px ' +
                             -this.imagePosition.y + 'px',
    };
  }
}

