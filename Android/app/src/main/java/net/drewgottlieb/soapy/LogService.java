package net.drewgottlieb.soapy;

import android.app.Service;

import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;
import android.widget.Toast;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class LogService extends Service {
    protected String TAG = "LogService";

    public static final String LOG_INTENT = "new.drewgottlieb.LOG_INTENT";
    public static final String LOG_INTENT_EXTRA_EVENT_INDEX = "EVENT_INDEX";

    private final LogBinder mBinder = new LogBinder();
    private Thread logListenThread = null;
    private ArrayList<LogEvent> events = new ArrayList<>();

    public LogService() {
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mBinder;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        return START_STICKY;
    }

    @Override
    public void onCreate() {
        logListenThread = new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    int pid = android.os.Process.myPid();
                    Process process = new ProcessBuilder(
                            "logcat", "-v", "threadtime", "*:*").redirectErrorStream(true).start();
                    BufferedReader reader = new BufferedReader(
                            new InputStreamReader(process.getInputStream()));

                    String line;
                    while ((line = reader.readLine()) != null) {
                        if (!line.contains(String.valueOf(pid))) {
                            continue;
                        }

                        events.add(new LogEvent(line));

                        Intent intent = new Intent();
                        intent.setAction(LOG_INTENT);
                        intent.putExtra(LOG_INTENT_EXTRA_EVENT_INDEX, events.size() - 1);
                        sendBroadcast(intent);
                    }

                    Log.i(TAG, "Finished reading logcat lines.");
                } catch (IOException e) {
                    e.printStackTrace();
                    Toast.makeText(getApplicationContext(), e.getMessage(), Toast.LENGTH_LONG);
                }
            }
        });
        logListenThread.start();
    }

    @Override
    public void onDestroy() {
        if (logListenThread != null) {
            logListenThread.interrupt();
            logListenThread = null;
        }
    }

    public Iterator<LogEvent> getRecentEvents(int limit) {
        int size = events.size();
        final int start = (size > limit ? size - limit : 0);

        return new Iterator<LogEvent>() {
            private int index = start;
            private boolean finished = false;

            @Override
            public boolean hasNext() {
                if (finished) {
                    return false;
                }

                finished = (index >= events.size());
                return !finished;
            }

            @Override
            public LogEvent next() {
                if (index < events.size()) {
                    return events.get(index++);
                } else {
                    finished = true;
                    return null;
                }
            }

            @Override
            public void remove() {
                // nothing
            }
        };
    }

    public int numEvents() {
        return events.size();
    }

    public LogEvent getEvent(int index) {
        return events.get(index);
    }

    public class LogBinder extends Binder {
        LogService getService() {
            return LogService.this;
        }
    }

    public static class LogEvent {
        private static final String TAG = "LogEvent";
        private static final Pattern pattern = Pattern.compile(
                "^([\\d-]+)\\s+([\\w:.]+)\\s+(\\d+)\\s+(\\d+)\\s+(\\w)\\s+([^\\s:]+)\\s*:\\s+(.*)");

        private String rawEvent;
        private String dateGroup;
        private String timeGroup;
        private String type;
        private String tag;
        private String message;

        public LogEvent(String rawEvent) {
            this.rawEvent = rawEvent;

            Matcher matcher = pattern.matcher(rawEvent);
            if (matcher.find()) {
                dateGroup = matcher.group(1);
                timeGroup = matcher.group(2);
                type = matcher.group(5);
                tag = matcher.group(6);
                message = matcher.group(7);
            } else if (!rawEvent.contains("Failed to parse log event")) {
                Log.w(TAG, "Failed to parse log event: \"" + rawEvent + "\"");
            }
        }

        public String getRawEvent() {
            return rawEvent;
        }

        @Override
        public String toString() {
            return getRawEvent();
        }

        public String getDateGroup() {
            return dateGroup;
        }

        public String getTimeGroup() {
            return timeGroup;
        }

        public String getTypeCharacter() {
            return type;
        }

        public String getTag() {
            return tag;
        }

        public String getMessage() {
            return message;
        }
    }
}
