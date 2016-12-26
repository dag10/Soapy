import {Injectable} from 'angular2/core';

import {SnackbarComponent} from './snackbar';


@Injectable()
export class SnackbarService {
  private _snackbar: SnackbarComponent;
  private _timeout: number = 2000;

  public registerSnackbarComponent(snackbar: SnackbarComponent) {
    this._snackbar = snackbar;
  }

  public showMessage(message: string) {
    this._snackbar.display({
      message: message,
      timeout: this._timeout,
    });
  }

  public showUndo(message: string, undo: () => void) {
    this.showAction(message, 'Undo', undo);
  }

  public showAction(
      message: string, actionText: string, action: () => void) {
    var actionPerformed = false;

    this._snackbar.display({
      message: message,
      timeout: this._timeout,
      actionText: actionText,
      actionHandler: () => {
        if (!actionPerformed) {
          actionPerformed = true;
          action();
        }
      },
    });
  }
}

