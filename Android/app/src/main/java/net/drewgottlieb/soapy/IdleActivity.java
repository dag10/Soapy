package net.drewgottlieb.soapy;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.KeyEvent;

public class IdleActivity extends SoapyActivity {
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
    }

    @Override
    // TODO: Listen to an event from arduino, not from the volume key.
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if ((keyCode == KeyEvent.KEYCODE_VOLUME_UP)) {
            rfidTapped("12345");
            return true;
        }

        return false;
    }

}
