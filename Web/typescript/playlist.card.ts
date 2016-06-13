import {
  EventEmitter,
  Component,
  Input,
  Output,
  ElementRef,
  AfterViewInit,
  AfterViewChecked,
  ChangeDetectorRef} from 'angular2/core';

import {Playlist} from './soapy.interfaces';
import {StaticData} from './StaticData';

declare var jQuery: JQueryStatic;


@Component({
  selector: 'playlist-card',
  template: StaticData.templates.PlaylistCard,
  host: {
    '[class.collapsed]': '!expanded',
    '[class.hidden]': '!playlists',
  },
})
export class PlaylistCardComponent implements AfterViewInit, AfterViewChecked {
  @Output() playlistSelected: EventEmitter<Playlist> = new EventEmitter();

  private $el: JQuery;
  private _playlists: Playlist[];
  private _selectedPlaylist: Playlist = null;
  private _formerlySelectedPlaylist: Playlist = null;

  constructor(private el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
  }

  public ngAfterViewChecked() {
    // Adds ripple effects to list items.
    (<any>window).componentHandler.upgradeElements(this.el.nativeElement);
  }

  public ngAfterViewInit() {
    this.hide();
  }

  public hide() {
    this.$el.hide();
  }

  public show() {
    this.$el.fadeIn();
  }

  @Input()
  public set playlists(playlists: Playlist[]) {
    this._playlists = playlists;

    if (playlists) {
      this.show();
    }
  }

  public get playlists(): Playlist[] {
    return this._playlists;
  }

  @Input()
  public set selectedPlaylist(playlist: Playlist) {
    this._selectedPlaylist = playlist;

    if (playlist !== null) {
      this._formerlySelectedPlaylist = playlist;
    }
  }

  public get selectedPlaylist(): Playlist {
    return this._selectedPlaylist;
  }

  public get formerlySelectedPlaylist(): Playlist {
    return this._formerlySelectedPlaylist;
  }

  public get expanded(): boolean {
    return !this.selectedPlaylist;
  }

  public cancel() {
    if (this.formerlySelectedPlaylist) {
      return this.selectPlaylist(this.formerlySelectedPlaylist);
    }

    return false;
  }

  public selectPlaylist(playlist: Playlist) {
    if (this.expanded || playlist === null) {
      this.playlistSelected.emit(playlist);
    }

    return false;
  }
}

