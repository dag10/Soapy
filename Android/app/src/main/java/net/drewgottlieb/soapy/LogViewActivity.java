package net.drewgottlieb.soapy;

import android.content.BroadcastReceiver;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.ServiceConnection;
import android.os.Bundle;
import android.os.IBinder;
import android.text.method.ScrollingMovementMethod;
import android.widget.TextView;

import java.util.Iterator;

public class LogViewActivity extends SoapyActivity {
    private LogService logService = null;
    private static final int NUM_INITIAL_EVENTS = 50;

    private BroadcastReceiver receiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            switch (intent.getAction()) {
                case LogService.LOG_INTENT:
                    int index = intent.getIntExtra(LogService.LOG_INTENT_EXTRA_EVENT_INDEX, -1);
                    if (index < 0) {
                        return;
                    }
                    if (logService != null) {
                        handleEvent(logService.getEvent(index));
                    }
                    break;
            }
        }
    };

    protected ServiceConnection logServiceConnection = new ServiceConnection() {
        @Override
        public void onServiceConnected(ComponentName name, IBinder binder) {
            logService = ((LogService.LogBinder) binder).getService();
            handleEvents(logService.getRecentEvents(NUM_INITIAL_EVENTS));
        }

        @Override
        public void onServiceDisconnected(ComponentName name) {
            logService = null;
        }
    };

    protected void handleEvents(Iterator<LogService.LogEvent> iter) {
        TextView logView = (TextView) findViewById(R.id.log_text_view);

        while (iter.hasNext()) {
            LogService.LogEvent e = iter.next();
            logView.append("\n" + formatLogEvent(e));
        }
    }

    protected void handleEvent(LogService.LogEvent event) {
        TextView logView = (TextView) findViewById(R.id.log_text_view);
        logView.append("\n" + formatLogEvent(event));
    }

    protected String formatLogEvent(LogService.LogEvent event) {
        return event.getTypeCharacter() + " " + event.getTimeGroup() +
               " [" + event.getTag() + "] " + event.getMessage();
    }

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        SoapyPreferences.createInstance(getApplicationContext());
        setContentView(R.layout.activity_log_view);

        TextView logView = (TextView) findViewById(R.id.log_text_view);
        logView.setText("");
        logView.setMovementMethod(new ScrollingMovementMethod());
    }

    @Override
    protected void onResume() {
        super.onResume();
        IntentFilter filter = new IntentFilter();
        filter.addAction(LogService.LOG_INTENT);
        registerReceiver(receiver, filter);
        bindService(logServiceIntent, logServiceConnection, Context.BIND_AUTO_CREATE);
    }

    @Override
    protected void onPause() {
        super.onPause();
        unregisterReceiver(receiver);
        unbindService(logServiceConnection);
    }
}
