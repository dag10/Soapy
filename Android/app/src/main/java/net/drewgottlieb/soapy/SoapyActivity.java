package net.drewgottlieb.soapy;

import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;


public class SoapyActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        ArduinoService.EnsureStarted(this);
    }
}
