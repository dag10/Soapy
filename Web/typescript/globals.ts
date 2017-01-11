export var MobileWidth = 500;

export function IsMobile(): boolean {
  return (<any>window).outerWidth <= MobileWidth;
}

