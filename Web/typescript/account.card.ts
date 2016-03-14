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
export class AccountCardComponent implements OnInit {
  @Input() playlists: Playlist[];
  @Output() unpair: EventEmitter<any> = new EventEmitter();

  public loadingSpotifyAuth: boolean = false;
  public spotifyAuthUrl: string = StaticData.spotifyAuthUrl;

  private _user: User;
  private imageLoaded: EventEmitter<any> = new EventEmitter();
  private imageSize: Size = { width: 115, height: 75 };
  private imagePosition: Position = { x: 0, y: 0 };

  constructor(private _changeDetector: ChangeDetectorRef) {}

  public ngOnInit() {
    console.info('Init...');

    this.imageLoaded.subscribe((img) => {
      // Aim for the focus of the image being to the left of the start of
      // the slant, except extending about 1/3 into the slant.
      //var minWidth = 65 + ((115 - 65) * 0.3);
      //var minWidth = 115;
      var minWidth = 65;
      var maxWidth = 115;
      var height = 75;

      var options: SmartCrop.CropOptions = {
        minScale: 0.7,
        width: minWidth,
        height: height,
      };

      console.info('Cropping...'); // TODO TMP

      SmartCrop.crop(img, options, (res: SmartCrop.CropResult) => {
        var crop = res.topCrop;

        console.info('IMG:', { width: img.width, height: img.height }); // TODO TMP
        console.info('ORIG CROP:', crop); // TODO TMP

        //var A = img.width;
        //var S = maxWidth;
        //var F = crop.width;

        //this.imageSize.width = S * A / F;

        var oldSize = { width: crop.width, height: crop.height };

        // Stretch width from target 65px to final 115px.
        // Also stretch height proportionally.
        crop.width = crop.width * (maxWidth / minWidth);
        crop.height = crop.height * (maxWidth / minWidth);

        console.info('WIDENED CROP:', crop); // TODO TMP

        if (crop.width > img.width) {
          // If stretched width is too wide, scale width and height down.
          crop.height = crop.height * (img.width / crop.width);
          crop.width = img.width;
          console.info('SHRUNKEN CROP:', crop); // TODO TMP

          // If height changed, shift y accordingly.
          crop.y = crop.y * (crop.height / oldSize.height);
          console.info('Y-SHIFTED CROP:', crop); // TODO TMP
        }

        //// If stretched width sticks off of right edge, move it leftwards.
        //if (crop.x + crop.width > img.width) {
          //crop.x = img.width - crop.width;
          //console.info('X-SHIFTED CROP:', crop); // TODO TMP
        //}

        // If stretched width sticks off of right edge, shrink it a bit.
        if (crop.x + crop.width > img.width) {
          var recentSize = { width: crop.width, height: crop.height };

          crop.width = img.width - crop.x;
          crop.height = crop.height * (crop.width / recentSize.width);

          crop.y += ((recentSize.height - crop.height) / 2);

          console.info('SHRUNKEN CROP INSTEAD OF X-SHIFT:', crop); // TODO TMP
        }

        this.imageSize.width = maxWidth * (img.width / crop.width);
        this.imageSize.height = this.imageSize.width;

        this.imagePosition.x = crop.x * (this.imageSize.width / img.width);
        this.imagePosition.y = crop.y * (this.imageSize.height / img.height);

        this._changeDetector.detectChanges();

        console.info('Image Size:', this.imageSize);
        console.info('Image Position:', this.imagePosition);

        return;

        var crop = res.topCrop;

        console.info('IMG:', { width: img.width, height: img.height }); // TODO TMP
        console.info('CROP:', crop); // TODO TMP
        
        var imageScale = crop.width / img.width;

        console.info('Initial widening...');

        crop.width = crop.width * (maxWidth / minWidth);
        console.info('CROP:', crop); // TODO TMP

        if (crop.width > img.width) {
          console.info('New width too large. Adjusting size...');
          //var oldYCenter = crop.y + (crop.height / 2);
          var oldCropHeight = crop.height;
          crop.height = crop.height * (img.width / crop.width);
          crop.y += ((oldCropHeight - crop.height) / 2);
          console.info('Old Crop Height:', oldCropHeight);
          console.info('New Crop Height:', crop.height);
          //var newYCenter = oldYCenter * (img.width / crop.width);
          //crop.y = newYCenter - (crop.height / 2);
          crop.width = img.width;
          imageScale = crop.width / img.width;
          console.info('CROP:', crop); // TODO TMP
        }

        if (crop.x + crop.width > img.width) {
          console.info('New x-position too wide with new width. Moving...');
          crop.x = img.width - crop.width;
          console.info('CROP:', crop); // TODO TMP
        }

        //var largestDim = img.width > img.height ? img.width : img.height;

        this.imageSize.width = maxWidth / imageScale;
        this.imageSize.height = (img.height / img.width) * maxWidth / imageScale;
        this.imagePosition.x = crop.x * (crop.width / img.width);
        this.imagePosition.y = crop.y * (crop.height / img.height);
        console.info('Image Size:', this.imageSize);
        console.info('Image Position:', this.imagePosition);
        this._changeDetector.detectChanges();

        console.info('SCALE:', imageScale); // TODO TMP
      });
    });
  }

  @Input()
  public set user(user: User) {
    var oldImage = this.image;
    this._user = user;

    console.info('setUser...'); // TODO TMP
    if (this.image && this.image !== oldImage) {
      console.info('image change...'); // TODO TMP
      var img = new Image();
      img.crossOrigin = "Anonymous";
      img.onload = () => {
        console.info('OnLoad...'); // TODO TMP
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
      //return 'https://scontent-iad3-1.xx.fbcdn.net/hphotos-xat1/v/t1.0-9/12208495_10102454385528521_4749095086285673716_n.jpg?oh=a8fa10b3f3d83fed66c50b16393d73ae&oe=574C275C';
      //return 'https://scontent-ord1-1.xx.fbcdn.net/hphotos-xpt1/v/t1.0-9/11392862_1036168069741699_5167069104172901994_n.jpg?oh=bffb248fea9bde761f982787486cc6cd&oe=57945261';

      if ((<any>window)['IMG_URL']) {
        return (<any>window).IMG_URL;
      }

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

