package net.drewgottlieb.soapy;

import android.app.Activity;
import android.app.Service;
import android.content.ComponentName;
import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

import com.physicaloid.lib.Physicaloid;
import com.physicaloid.lib.usb.driver.uart.ReadLisener;

import java.io.UnsupportedEncodingException;

public class ArduinoService extends Service implements ReadLisener {
    private static final String TAG = "ArduinoService";
    private static ComponentName serviceComponentName = null;

    public static void EnsureStarted(Activity activity) {
        if (serviceComponentName == null) {
            serviceComponentName = activity.startService(new Intent(activity, ArduinoService.class));
        }
    }

    public class ArduinoBinder extends Binder {
        ArduinoService getService() {
            return ArduinoService.this;
        }
    }

    private final ArduinoBinder mBinder = new ArduinoBinder();
    private final Physicaloid arduino = new Physicaloid(this);
    private String buffer = "";
    private boolean[] lampStatus = new boolean[2];

    public ArduinoService() {
    }

    private void handleReceivedString(String str) {
        Log.i(TAG, "Received: \"" + str + "\"");

        if (str.startsWith("door[")) {
            int idx = str.indexOf(']', 5);
            if (idx < 0) {
                Log.e(TAG, "Incorrect door format.");
                return;
            }

            int doorId = Integer.parseInt(str.substring(5, idx));
            boolean closed = str.contains("closed");

            setLamp(doorId, closed);
        }
    }

    @Override
    public void onRead(int size) {
        byte[] buf = new byte[size];
        String readString;

        arduino.read(buf, size);
        try {
            readString = new String(buf, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG, "Failed to read arduino string.");
            return;
        }

        if (buffer == null) {
            Log.w(TAG, "Arduino sent null string.");
            return;
        }

        buffer += readString;

        int nlIdx = buffer.indexOf('\n');
        while (nlIdx >= 0) {
            if (nlIdx == 0) {
                Log.w(TAG, "Received 0-length message from arduino.");
                if (buffer.length() > 0) {
                    buffer = buffer.substring(1);
                    continue;
                } else {
                    buffer = "";
                    break;
                }
            }

            handleReceivedString(buffer.substring(0, nlIdx - 1));

            if (buffer.length() > nlIdx) {
                buffer = buffer.substring(nlIdx + 1);
                nlIdx = buffer.indexOf('\n');
            } else {
                buffer = "";
                nlIdx = -1;
            }
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mBinder;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Log.v(TAG, "Service started!");
        return START_STICKY;
    }

    @Override
    public void onCreate() {
        connect();
    }

    @Override
    public void onDestroy() {
        arduino.clearReadListener();
        arduino.close();
    }

    private void sendMessage(String str) {
        Log.i(TAG, "Sending: \"" + str + "\"");

        str += "\n";

        if (!arduino.isOpened()) {
            Log.e(TAG, "Tried to send message but arduino connection is closed.");
            return;
        }

        byte[] buf = str.getBytes();
        arduino.write(buf, buf.length);
    }

    private void initializeConnection() {
        for (int i = 0; i < lampStatus.length; i++) {
            sendLamp(i);
        }

        sendMessage("poll");
    }

    private void setLamp(int lampId, boolean on) {
        if (lampStatus[lampId] != on) {
            lampStatus[lampId] = on;
            sendLamp(lampId);
        }
    }

    private void sendLamp(int lampId) {
        sendMessage("lamp[" + lampId + "]: " + (lampStatus[lampId] ? "on" : "off"));
    }

    private void connect() {
        new Thread(new Runnable() {
            public void run() {
                while (!arduino.isOpened()) {
                    if (arduino.open()) {
                        arduino.addReadListener(ArduinoService.this);
                        Log.i(TAG, "Connected to arduino.");
                        initializeConnection();
                        break;
                    }

                    try {
                        Thread.sleep(1000);
                    } catch (Exception e) {
                        // don't care
                    }
                }
            }
        }).start();
    }
}
