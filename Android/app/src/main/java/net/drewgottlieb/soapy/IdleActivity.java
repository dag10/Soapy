package net.drewgottlieb.soapy;

import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.ServiceConnection;
import android.media.AudioManager;
import android.os.Bundle;
import android.os.IBinder;
import android.view.View;
import android.util.Log;
import android.view.KeyEvent;

public class IdleActivity extends SoapyActivity implements View.OnLongClickListener {
    private ArduinoService arduinoService = null;
    private SoapyPreferences preferences = null;

    protected ServiceConnection arduinoServiceConnection = new ServiceConnection() {
        @Override
        public void onServiceConnected(ComponentName name, IBinder binder) {
            arduinoService = ((ArduinoService.ArduinoBinder) binder).getService();
            arduinoService.connect();
        }

        @Override
        public void onServiceDisconnected(ComponentName name) {
            arduinoService = null;
        }
    };

    @Override
    protected void rfidTapped(String rfid) {
        Log.w("Soapy", "RFID tapped: " + rfid);

        Intent intent = new Intent(this, PlaylistSelectionActivity.class);
        intent.putExtra(PlaylistSelectionActivity.EXTRA_RFID, rfid);
        startActivity(intent);
    }

    protected void createApplicationSingletons() {
        preferences = SoapyPreferences.createInstance(getApplicationContext());
        SoapySoundPlayer.createInstance(getApplicationContext());
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        createApplicationSingletons();

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_idle);

        View statusStrip = findViewById(R.id.status_strip);
        statusStrip.setOnLongClickListener(this);

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
        if (view == findViewById(R.id.status_strip)) {
            Intent intent = new Intent(this, LogViewActivity.class);
            startActivity(intent);
        }

        return true;
    }

    @Override
    protected void onResume() {
        super.onResume();
        bindService(arduinoServiceIntent, arduinoServiceConnection, Context.BIND_AUTO_CREATE);
    }

    @Override
    protected void onPause() {
        super.onPause();
        unbindService(arduinoServiceConnection);
    }

    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);

        if (arduinoService != null) {
            arduinoService.connect();
        }
    }
}
