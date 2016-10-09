import {
  Component,
  Input,
  Output,
  ElementRef,
  AfterViewInit,
  EventEmitter,
  ChangeDetectorRef} from 'angular2/core';

import {Track, Playback, Playlist} from './soapy.interfaces';
import {SpinnerComponent} from './spinner';
import {StaticData} from './StaticData';
import {SoapyService} from './soapy.service';

declare var jQuery: JQueryStatic;


export interface DisplayedTrack extends Track {
  displayedPosition?: number;
}


@Component({
  providers: [SoapyService],
  directives: [
    SpinnerComponent,
  ],
  selector: 'playback-card',
  host: {'(window:scroll)': 'handleScroll($event)'},
  template: StaticData.templates.PlaybackCard,
})
export class PlaybackCardComponent implements AfterViewInit {
  @Output() playbackUpdated: EventEmitter<Playback> = new EventEmitter<Playback>();

  private $el: JQuery;
  private _selectedPlaylist: Playlist = null;
  private _playback: Playback = null;

  constructor(private _soapyService: SoapyService,
              private el: ElementRef,
              private _changeDetector: ChangeDetectorRef) {
    this.$el = jQuery(this.el.nativeElement);
  }

  public ngAfterViewInit() {
    (<any>window).Stickyfill.add(this.$el.find('.sticky-header')[0]);
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

      this._soapyService
        .fetchPlaylistWithTracklist(this._selectedPlaylist.id)
        .subscribe((fetchedPlaylist: Playlist) => {
          this._selectedPlaylist = fetchedPlaylist;
          this._changeDetector.detectChanges();
        });
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
    return (this.playback !== null
            && this.selectedPlaylist !== null
            && !!this.selectedPlaylist.tracklist);
  }

  public toggleShuffle() {
    var newPlayback = jQuery.extend({}, this.playback);
    newPlayback.shuffle = !newPlayback.shuffle;

    this.playbackUpdated.emit(newPlayback);

    return false;
  }

  public get validTracklist(): DisplayedTrack[] {
    var tracks: DisplayedTrack[] = [];
    var nextPosition = 1;
    this._selectedPlaylist.tracklist.forEach(function(track: DisplayedTrack) {
      if (track.valid) {
        if (!track.local) {
          track.displayedPosition = nextPosition;
          nextPosition++;
        }
        tracks.push(track);
      }
    });
    return tracks;
  }

  public get hasInvalidTracks(): boolean {
    if (!this._selectedPlaylist ||
        !this._selectedPlaylist.tracklist) {
      return false;
    }

    for (var i = 0; i < this._selectedPlaylist.tracklist.length; i++) {
      if (!this._selectedPlaylist.tracklist[i].valid) {
        return true;
      }
    }

    return false;
  }

  public get hasLocalTracks(): boolean {
    if (!this._selectedPlaylist ||
        !this._selectedPlaylist.tracklist) {
      return false;
    }

    for (var i = 0; i < this._selectedPlaylist.tracklist.length; i++) {
      if (this._selectedPlaylist.tracklist[i].local &&
          this._selectedPlaylist.tracklist[i].valid) {
        return true;
      }
    }

    return false;
  }

  public get tracklistIsIncomplete(): boolean {
    if (!this._selectedPlaylist ||
        !this._selectedPlaylist.tracklist) {
      return false;
    }

    var actualTracks = this._selectedPlaylist.tracklist.length;
    var intendedTracks = this._selectedPlaylist.tracks;

    return actualTracks < intendedTracks && intendedTracks > 100;
  }

  public get tracklistHasPlayableSongs(): boolean {
    if (!this._selectedPlaylist ||
        !this._selectedPlaylist.tracklist) {
      return false;
    }

    for (var i = 0; i < this._selectedPlaylist.tracklist.length; i++) {
      if (!this._selectedPlaylist.tracklist[i].local &&
          this._selectedPlaylist.tracklist[i].valid) {
        return true;
      }
    }

    return false;
  }

  private handleScroll(event) {
    this.updateStickyHeader();
  }

  private updateStickyHeader() {
    var $header = this.$el.find('.sticky-header');
    var stickyClass = 'stickied';

    var pos = $header.offset().top - jQuery(window).scrollTop();
    var top = parseInt($header.css('top'), 10);
    var atTop = (pos === top);

    if ($header.hasClass(stickyClass) && !atTop) {
      $header.removeClass(stickyClass);
    } else if (!$header.hasClass(stickyClass) && atTop) {
      $header.addClass(stickyClass);
    }
  }
}

