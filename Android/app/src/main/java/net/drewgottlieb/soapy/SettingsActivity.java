package net.drewgottlieb.soapy;

import android.os.Bundle;

public class SettingsActivity extends SoapyActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        SoapyPreferences.createInstance(getApplicationContext());
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);
    }
}
