import {Component, ElementRef, OnInit, AfterViewChecked} from 'angular2/core';

import {StaticData} from './StaticData';
import {SnackbarService} from './snackbar.service';


export interface SnackbarData {
  message: string;
  timeout: number;
  actionHandler?: () => void;
  actionText?: string;
}

interface MaterialSnackbar {
  showSnackbar: (data: SnackbarData) => void;
  active: boolean;
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

  public get materialSnackbar(): MaterialSnackbar {
    return (<any>this.snackbarElement).MaterialSnackbar;
  }

  public display(data: SnackbarData) {
    if (data.actionHandler) {
      var originalActionHandler: () => void = data.actionHandler;
      data.actionHandler = () => {
        this.hide();
        originalActionHandler();
      };
    }

    this.materialSnackbar.showSnackbar(data);
  }

  public hide() {
    this.snackbarElement.classList.remove('mdl-snackbar--active');
    this.materialSnackbar.active = false;
  }

  public get active(): boolean {
    return this.materialSnackbar.active;
  }
}

