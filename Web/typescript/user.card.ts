import {
  EventEmitter,
  Component,
  Input,
  Output,
  ChangeDetectorRef} from 'angular2/core';

import {User} from './users.service';
import {StaticData} from './StaticData';


@Component({
  selector: 'user-card',
  template: StaticData.templates.UserCard,
})
export class UserCardComponent {
  @Output() unpairRFID: EventEmitter<any> = new EventEmitter();

  private _user: User;

  constructor(private _changeDetector: ChangeDetectorRef) {}

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
    if (this.user && this.user.avatar) {
      return this.user.avatar;
    }

    return null;
  }

  public get style(): Object {
    return {
      'background-image': 'url(\'' + this.image + '\')',
    };
  }
}

