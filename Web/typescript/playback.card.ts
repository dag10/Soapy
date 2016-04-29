import {
  Component,
  Input,
  Output,
  ElementRef,
  AfterViewInit,
  EventEmitter,
  ChangeDetectorRef} from 'angular2/core';

import {Playback, Playlist} from './soapy.interfaces';
import {SpinnerComponent} from './spinner';
import {StaticData} from './StaticData';

declare var jQuery: JQueryStatic;


@Component({
  directives: [
    SpinnerComponent,
  ],
  selector: 'playback-card',
  template: StaticData.templates.PlaybackCard,
  host: {
    '[class.loading]': '!loaded',
  },
})
export class PlaybackCardComponent implements AfterViewInit {
  @Output() playbackUpdated: EventEmitter<Playback> = new EventEmitter();

  private $el: JQuery;
  private _selectedPlaylist: Playlist = null;
  private _playback: Playback = null;

  constructor(private el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
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
  public set selectedPlaylist(playlist: Playlist) {
    this._selectedPlaylist = playlist;

    if (this._selectedPlaylist === null) {
      this.hide();
    } else {
      this.show();
    }

    this._changeDetector.detectChanges();
  }

  public get selectedPlaylist(): Playlist {
    return this._selectedPlaylist;
  }

  @Input()
  public set playback(playback: Playback) {
    this._playback = playback;
    this._changeDetector.detectChanges();
  }

  public get playback(): Playback {
    return this._playback;
  }

  public get loaded(): boolean {
    return (this.playback !== null);
  }

  public toggleShuffle() {
    var newPlayback = jQuery.extend({}, this.playback);
    newPlayback.shuffle = !newPlayback.shuffle;

    this.playbackUpdated.emit(newPlayback);

    return false;
  }
}

