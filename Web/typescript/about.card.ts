import {Component, ElementRef} from 'angular2/core';
import {StaticData} from './StaticData';

declare var jQuery: JQueryStatic;


@Component({
  selector: 'about-card',
  template: StaticData.templates.AboutCard,
})
export class AboutCardComponent {
  private $el: JQuery;

  constructor(private el: ElementRef) {
    this.$el = jQuery(this.el.nativeElement);
    (<any>window).J = this.$el; // TODO TMP
  }

  public get $more(): JQuery {
    return this.$el.find('.more');
  }

  public get $expandBtn(): JQuery {
    return this.$el.find('.expand-btn');
  }

  public expand() {
    this.$expandBtn.hide();
    this.$el.css('height', this.$el.outerHeight() + this.$more.outerHeight());

    //this.$el.animate({
      //height: this.$el.outerHeight() + this.$more.outerHeight(),
    //});

    return false;
  }
}

