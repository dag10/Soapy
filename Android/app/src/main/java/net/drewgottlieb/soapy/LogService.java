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
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.Iterator;
import java.util.List;
import java.util.TimeZone;
import java.util.Timer;
import java.util.TimerTask;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class LogService extends Service {
    protected String TAG = "LogService";

    public static final String LOG_INTENT = "new.drewgottlieb.LOG_INTENT";
    public static final String LOG_INTENT_EXTRA_EVENT_INDEX = "EVENT_INDEX";

    public static final int LOG_UPLOAD_INTERVAL = 5; // Seconds between log uploads

    private final LogBinder mBinder = new LogBinder();
    private Thread logListenThread = null;
    private ArrayList<LogEvent> events = new ArrayList<>();
    private Timer uploadTimer = null;
    private int nextIndexToUpload = 0;

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

        uploadTimer = new Timer();
        uploadTimer.scheduleAtFixedRate(new TimerTask() {
            @Override
            public void run() {
                uploadLogs();
            }
        }, 0, LOG_UPLOAD_INTERVAL * 1000);
    }

    @Override
    public void onDestroy() {
        if (logListenThread != null) {
            logListenThread.interrupt();
            logListenThread = null;
        }

        if (uploadTimer != null) {
            uploadTimer.cancel();
            uploadTimer = null;
        }
    }

    protected void uploadLogs() {
        int numEventsToUpload = events.size() - nextIndexToUpload;
        if (numEventsToUpload <= 0) {
            return;
        }

        ArrayList<LogEvent> eventsToUpload = new ArrayList<>(numEventsToUpload);
        for (int i = nextIndexToUpload; i < nextIndexToUpload + numEventsToUpload; i++) {
            eventsToUpload.add(events.get(i));
        }

        SoapyWebAPI.getInstance().uploadLogs(eventsToUpload);

        nextIndexToUpload += numEventsToUpload;
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
        public static enum Level {
            ERROR, WARN, INFO, DEBUG, VERBOSE,
            UNKNOWN
        }

        private static final String TAG = "LogEvent";
        private static final Pattern pattern = Pattern.compile(
                "^([\\d-]+)\\s+([\\w:.]+)\\s+(\\d+)\\s+(\\d+)\\s+(\\w)\\s+([^\\s:]+)\\s*:\\s+(.*)");
        private static final SimpleDateFormat dateFormat = new SimpleDateFormat("MM-dd-yyyy HH:mm:ss.S");
        private static final String yearString = "-" + Calendar.getInstance().get(Calendar.YEAR);

        private String rawEvent;
        private String dateGroup;
        private String timeGroup;
        private Date date;
        private String levelCode;
        private Level level;
        private String tag;
        private String message;

        public static Level levelForLevelCode(String code) {
            switch (code) {
                case "E":
                    return Level.ERROR;
                case "W":
                    return Level.WARN;
                case "I":
                    return Level.INFO;
                case "D":
                    return Level.DEBUG;
                case "V":
                    return Level.VERBOSE;
                default:
                    return Level.UNKNOWN;
            }
        }

        public LogEvent(String rawEvent) {
            this.rawEvent = rawEvent;

            Matcher matcher = pattern.matcher(rawEvent);
            if (matcher.find()) {
                dateGroup = matcher.group(1);
                timeGroup = matcher.group(2);

                try {
                    date = dateFormat.parse(dateGroup + yearString + " " + timeGroup);
                } catch (ParseException e) {
                    Log.e(TAG, "Failed to parse event date or time: \"" +
                          dateGroup + " " + timeGroup + "\"");
                    date = null;
                }

                levelCode = matcher.group(5);
                level = levelForLevelCode(levelCode);

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

        public Date getDate() {
            return date;
        }

        public Level getLevel() {
            return level;
        }

        public String getLevelCode() {
            return levelCode;
        }

        public String getTag() {
            return tag;
        }

        public String getMessage() {
            return message;
        }
    }
}
