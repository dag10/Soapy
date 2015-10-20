package net.drewgottlieb.soapy;

import android.content.Context;
import android.content.Intent;
import android.media.AudioManager;
import android.os.Bundle;
import android.view.View;
import android.util.Log;
import android.view.KeyEvent;

public class IdleActivity extends SoapyActivity implements View.OnLongClickListener {
    private SoapyPreferences preferences = null;

    @Override
    protected void rfidTapped(String rfid) {
        Log.w("Soapy", "RFID tapped: " + rfid);

        Intent intent = new Intent(this, PlaylistSelectionActivity.class);
        intent.putExtra(PlaylistSelectionActivity.EXTRA_RFID, rfid);
        startActivity(intent);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        preferences = SoapyPreferences.createInstance(getApplicationContext());
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_idle);

        View view = getWindow().getDecorView().findViewById(android.R.id.content);
        view.setOnLongClickListener(this);

        // Set system media volume to max
        AudioManager audio = (AudioManager) getSystemService(Context.AUDIO_SERVICE);
        int maxVolume = audio.getStreamMaxVolume(AudioManager.STREAM_MUSIC);
        audio.setStreamVolume(AudioManager.STREAM_MUSIC, maxVolume, 0);
    }

    @Override
    // TODO: Listen to an event from arduino, not from the volume key.
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_VOLUME_UP && preferences.getEnableVolumeShortcut()) {
            rfidTapped("12345");
            return true;
        }

        return false;
    }

    public boolean onLongClick(View view) {
        Intent intent = new Intent(this, LogViewActivity.class);
        startActivity(intent);

        return true;
    }
}
