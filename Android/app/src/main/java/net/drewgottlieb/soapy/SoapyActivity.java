package net.drewgottlieb.soapy;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;


public class SoapyActivity extends AppCompatActivity {
    protected String TAG = "SoapyActivity";

    protected void rfidTapped(String rfid) {
        // nothing
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
                case ArduinoService.DOOR_INTENT:
                    // TODO: Anything?
                    break;
            }
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        startService(new Intent(this, ArduinoService.class));
    }

    @Override
    protected void onResume() {
        super.onResume();
        IntentFilter filter = new IntentFilter();
        filter.addAction(ArduinoService.RFID_INTENT);
        registerReceiver(receiver, filter);
    }

    @Override
    protected void onPause() {
        super.onPause();
        unregisterReceiver(receiver);
    }
}
