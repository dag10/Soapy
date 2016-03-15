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

    @Override
    protected void rfidTapped(String rfid) {
        Log.w("Soapy", "RFID tapped: " + rfid);

        Intent intent = new Intent(this, PlaylistSelectionActivity.class);
        intent.putExtra(PlaylistSelectionActivity.EXTRA_RFID, rfid);
        startActivity(intent);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_idle);

        View statusStrip = findViewById(R.id.status_strip);
        statusStrip.setOnLongClickListener(this);
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
}
