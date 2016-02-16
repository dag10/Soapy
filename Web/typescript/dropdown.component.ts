import {Component, EventEmitter, Input, Output} from 'angular2/core';

import {SelectableOption} from './soapy.interfaces';


@Component({
  selector: 'soapy-dropdown',
  templateUrl: '/app/dropdown.component.html',
})
export class DropdownComponent {
  @Input() items: SelectableOption[];
  @Input() selectedItem: SelectableOption;
  @Output() selectedItemChange = new EventEmitter();

  get selectedItemId(): String {
    if (this.selectedItem == null) {
      return null;
    }

    return this.selectedItem.id;
  }

  set selectedItemId(id: String) {
    this.selectedItem = this.itemForId(id);
    this.selectedItemChange.next(this.selectedItem);
  }

  protected itemForId(id: String): SelectableOption {
    for (var item of this.items) {
      if (item.id === id) {
        return item;
      }
    }

    return null;
  }
}

