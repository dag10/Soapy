package net.drewgottlieb.soapy;

import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.media.AudioManager;
import android.os.Bundle;
import android.os.IBinder;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;


public class SoapyActivity extends AppCompatActivity implements StatusStrip.OnFragmentInteractionListener {
    protected String TAG = "SoapyActivity";

    protected Intent logServiceIntent;
    protected Intent arduinoServiceIntent;
    protected Intent spotifyServiceIntent;

    protected ArduinoService arduinoService = null;
    protected SoapyPreferences preferences = null;

    protected void rfidTapped(String rfid) {
    }

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

    private BroadcastReceiver receiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            switch (intent.getAction()) {
                case ArduinoService.RFID_INTENT:
                    int index = intent.getIntExtra("index", 0);
                    String rfid = intent.getStringExtra("rfid");
                    if (index == 0) {
                        rfidTapped(rfid);
                    }
                    break;
            }
        }
    };

    public void onCancelPressed() {
        finish();
    }

    protected void createApplicationSingletons() {
        preferences = SoapyPreferences.createInstance(getApplicationContext());
        SoapySoundPlayer.createInstance(getApplicationContext());
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        createApplicationSingletons();

        super.onCreate(savedInstanceState);

        logServiceIntent = new Intent(this, LogService.class);
        startService(logServiceIntent);

        arduinoServiceIntent = new Intent(this, ArduinoService.class);
        startService(arduinoServiceIntent);

        spotifyServiceIntent = new Intent(this, SpotifyService.class);
        startService(spotifyServiceIntent);

        // Set system media volume to max
        AudioManager audio = (AudioManager) getSystemService(Context.AUDIO_SERVICE);
        int maxVolume = audio.getStreamMaxVolume(AudioManager.STREAM_MUSIC);
        audio.setStreamVolume(AudioManager.STREAM_MUSIC, maxVolume, 0);
    }

    protected void goImmersive() {
        View decorView = getWindow().getDecorView();
        int uiOptions = View.SYSTEM_UI_FLAG_HIDE_NAVIGATION | View.SYSTEM_UI_FLAG_FULLSCREEN | View.SYSTEM_UI_FLAG_IMMERSIVE;
        decorView.setSystemUiVisibility(uiOptions);
    }

    @Override
    protected void onResume() {
        super.onResume();
        bindService(arduinoServiceIntent, arduinoServiceConnection, Context.BIND_AUTO_CREATE);
        IntentFilter filter = new IntentFilter();
        filter.addAction(ArduinoService.RFID_INTENT);
        registerReceiver(receiver, filter);
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
        //goImmersive();
}

    @Override
    protected void onPause() {
        super.onPause();
        unregisterReceiver(receiver);
        unbindService(arduinoServiceConnection);
    }

    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);

        if (arduinoService != null) {
            arduinoService.connect();
        }
    }
}
