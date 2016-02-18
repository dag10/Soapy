package net.drewgottlieb.soapy;

import android.content.Context;
import android.media.AudioManager;
import android.media.SoundPool;
import android.util.Log;

import java.util.HashMap;
import java.util.Map;

public class SoapySoundPlayer {
    private static String TAG = "SoapySoundPlayer";
    private static SoapySoundPlayer instance = null;

    private Context context;
    private SoundPool pool;
    private Map<Integer, Integer> soundIdMap = new HashMap<>();

    public static SoapySoundPlayer createInstance(Context context) {
        instance = new SoapySoundPlayer(context);
        return instance;
    }

    public static SoapySoundPlayer getInstance() {
        return instance;
    }

    public SoapySoundPlayer(Context context) {
        this.context = context;
        pool = new SoundPool(1, AudioManager.STREAM_MUSIC, 0);

        preloadSounds();
    }

    protected void preloadSounds() {
        getSoundId(R.raw.tap_29122_junggle_btn312);
        getSoundId(R.raw.success_29124_junggle_btn314);
        getSoundId(R.raw.error_28987_junggle_btn177);
    }

    protected int getSoundId(int resId) {
        if (soundIdMap.containsKey(resId)) {
            return soundIdMap.get(resId);
        }

        int soundId = pool.load(context, resId, 1);
        soundIdMap.put(resId, soundId);
        return soundId;
    }

    public synchronized void playSoundResource(int resId) {
        Log.i(TAG, "Playing sound resource: " + context.getResources().getResourceEntryName(resId));
        pool.play(getSoundId(resId), 1.f, 1.f, 0, 0, 1.f);
    }

    public void playTapSound() {
        playSoundResource(R.raw.tap_29122_junggle_btn312);
    }

    public void playSuccessSound() {
        playSoundResource(R.raw.success_29124_junggle_btn314);
    }

    public void playErrorSound() {
        playSoundResource(R.raw.error_28987_junggle_btn177);
    }
}
