import {Component, ElementRef, OnInit, AfterViewChecked} from 'angular2/core';

import {StaticData} from './StaticData';
import {SnackbarService} from './snackbar.service';


export interface SnackbarData {
  message: string;
  timeout: number;
  actionHandler?: (any) => void;
  actionText?: string;
}

@Component({
  selector: 'snackbar',
  template: StaticData.templates.Snackbar,
})
export class SnackbarComponent implements OnInit, AfterViewChecked {
  constructor(private _el: ElementRef,
              private _snackbarService: SnackbarService) {}

  public ngOnInit() {
    this._snackbarService.registerSnackbarComponent(this);
  }

  public ngAfterViewChecked() {
    (<any>window).componentHandler.upgradeElements(this._el.nativeElement);
  }

  public get snackbarElement(): HTMLElement {
    return this._el.nativeElement.querySelector('.mdl-snackbar');
  }

  public display(data: SnackbarData) {
    (<any>this.snackbarElement).MaterialSnackbar.showSnackbar(data);
  }
}

