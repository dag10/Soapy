package net.drewgottlieb.soapy;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;


public class SoapyActivity extends AppCompatActivity implements StatusStrip.OnFragmentInteractionListener {
    protected String TAG = "SoapyActivity";

    protected Intent logServiceIntent;
    protected Intent arduinoServiceIntent;
    protected Intent spotifyServiceIntent;

    protected void rfidTapped(String rfid) {
    }

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


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        logServiceIntent = new Intent(this, LogService.class);
        startService(logServiceIntent);

        arduinoServiceIntent = new Intent(this, ArduinoService.class);
        startService(arduinoServiceIntent);

        spotifyServiceIntent = new Intent(this, SpotifyService.class);
        startService(spotifyServiceIntent);
    }

    protected void goImmersive() {
        View decorView = getWindow().getDecorView();
        int uiOptions = View.SYSTEM_UI_FLAG_HIDE_NAVIGATION | View.SYSTEM_UI_FLAG_FULLSCREEN | View.SYSTEM_UI_FLAG_IMMERSIVE;
        decorView.setSystemUiVisibility(uiOptions);
    }

    @Override
    protected void onResume() {
        super.onResume();
        IntentFilter filter = new IntentFilter();
        filter.addAction(ArduinoService.RFID_INTENT);
        registerReceiver(receiver, filter);
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
        goImmersive();
    }

    @Override
    protected void onPause() {
        super.onPause();
        unregisterReceiver(receiver);
    }
}
