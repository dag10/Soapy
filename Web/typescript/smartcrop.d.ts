declare module "smartcrop" {
  export interface CropOptions {
    debug?: boolean;
    minScale?: number;
    width?: number;
    height?: number;
  }

  export interface Crop {
    x: number;
    y: number;
    width: number;
    height: number;
  }

  export interface CropResult {
    topCrop: Crop;
    crops: [Crop];
  }

  export function crop(
      image: HTMLImageElement | HTMLCanvasElement | HTMLVideoElement,
      options: CropOptions,
      callback: ((CropResult) => void)
      );
}

