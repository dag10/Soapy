package net.drewgottlieb.soapy;

import android.content.Context;
import android.content.res.TypedArray;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.ColorFilter;
import android.graphics.Paint;
import android.graphics.PixelFormat;
import android.graphics.drawable.Drawable;
import android.support.design.widget.FloatingActionButton;
import android.util.AttributeSet;

/**
 * Created by drew on 10/11/15.
 */
public class SoapyFab extends FloatingActionButton {
    /**
     * Class provided by http://stackoverflow.com/a/8831182/3333841
     */
    public static class FabTextDrawable extends Drawable {
        private final String text;
        private final Paint paint;
        private float posx, posy;

        public FabTextDrawable(String text, float posx, float posy) {
            this.text = text;
            this.paint = new Paint();
            paint.setColor(Color.WHITE);
            paint.setTextSize(14f);
            paint.setAntiAlias(true);
            paint.setFakeBoldText(true);
            paint.setStyle(Paint.Style.FILL);
            paint.setTextAlign(Paint.Align.LEFT);
            this.posx = posx;
            this.posy = posy;
        }

        @Override
        public void draw(Canvas canvas) {
            canvas.drawText(text, posx, posy, paint);
        }

        @Override
        public void setAlpha(int alpha) {
            paint.setAlpha(alpha);
        }

        @Override
        public void setColorFilter(ColorFilter cf) {
            paint.setColorFilter(cf);
        }

        @Override
        public int getOpacity() {
            return PixelFormat.TRANSLUCENT;
        }
    }

    public SoapyFab(Context context) {
        super(context);
    }

    public SoapyFab(Context context, AttributeSet attrs) {
        super(context, attrs);
        init(attrs);
    }

    private void init(AttributeSet attrs) {
        if (attrs == null) {
            return;
        }

        TypedArray a = getContext().obtainStyledAttributes(attrs, R.styleable.SoapyFab);
        setImageDrawable(new FabTextDrawable(a.getString(R.styleable.SoapyFab_android_text), -1.f, 14.f));
    }
}

