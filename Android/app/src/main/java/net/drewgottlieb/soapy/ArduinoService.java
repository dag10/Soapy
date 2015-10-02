package net.drewgottlieb.soapy;

import android.app.Service;
import android.content.Intent;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

import com.physicaloid.lib.Physicaloid;
import com.physicaloid.lib.usb.driver.uart.ReadLisener;

import java.io.UnsupportedEncodingException;

class MessageIndexFormatException extends Exception {
    public MessageIndexFormatException(String msg) {
        super("Invalid message format. No index found in \"" + msg + "\".");
    }
}

class MessageValueFormatException extends Exception {
    public MessageValueFormatException(String msg) {
        super("Invalid message format. No value found in \"" + msg + "\".");
    }
}

public class ArduinoService extends Service implements ReadLisener {
    public static final String RFID_INTENT = "new.drewgottlieb.RFID_INTENT";
    public static final String DOOR_INTENT = "new.drewgottlieb.DOOR_INTENT";

    private static String TAG = "ArduinoService";

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

    private static int getMessageIndex(String msg) throws MessageIndexFormatException {
        int startIdx = msg.indexOf('[');
        if (startIdx < 0) {
            throw new MessageIndexFormatException(msg);
        }

        int endIdx = msg.indexOf(']', startIdx);
        if (endIdx < 0) {
            throw new MessageIndexFormatException(msg);
        }

        try {
            return Integer.parseInt(msg.substring(startIdx + 1, endIdx));
        } catch (NumberFormatException e) {
            throw new MessageIndexFormatException(msg);
        }
    }

    private static String getMessageValue(String msg) throws MessageValueFormatException {
        int idx = msg.indexOf(": ");
        if (idx < 0 || msg.length() < idx + 3) {
            throw new MessageValueFormatException(msg);
        }

        return msg.substring(idx + 2);
    }

    private void handleReceivedString(String str) {
        Log.i(TAG, "Received: \"" + str + "\"");

        if (str.startsWith("door[")) {
            int doorId;
            boolean closed;

            try {
                doorId = getMessageIndex(str);
                closed = "closed".equals(getMessageValue(str));
            } catch (Exception e) {
                Log.e(TAG, "Failed to parse door message; " + e.getMessage());
                return;
            }

            setLamp(doorId, closed);

            Intent intent = new Intent();
            intent.setAction(DOOR_INTENT);
            intent.putExtra("index", doorId);
            intent.putExtra("closed", closed);
            sendBroadcast(intent);

        } else if (str.startsWith("rfid[")) {
            int rfidId;
            String rfid;

            try {
                rfidId = getMessageIndex(str);
                rfid = getMessageValue(str);
            } catch (Exception e) {
                Log.e(TAG, "Failed to parse rfid message; " + e.getMessage());
                return;
            }

            Intent intent = new Intent();
            intent.setAction(RFID_INTENT);
            intent.putExtra("index", rfidId);
            intent.putExtra("rfid", rfid);
            sendBroadcast(intent);

        } else if (str.startsWith("num_doors:")) {
            int numDoors;

            try {
                numDoors = Integer.parseInt(getMessageValue(str));
            } catch (Exception e) {
                Log.e(TAG, "Failed to parse num_doors; " + e.getMessage());
                return;
            }

            // TODO: Save number of doors

        } else if (str.startsWith("num_rfids:")) {
            int numRfids;

            try {
                numRfids = Integer.parseInt(getMessageValue(str));
            } catch (Exception e) {
                Log.e(TAG, "Failed to parse num_rfids; " + e.getMessage());
                return;
            }

            // TODO: Save number of RFID scanners

        } else if (str.startsWith("num_lamps:")) {
            int numLamps;

            try {
                numLamps = Integer.parseInt(getMessageValue(str));
            } catch (Exception e) {
                Log.e(TAG, "Failed to parse num_lamps; " + e.getMessage());
                return;
            }

            // TODO: Save number of lamps
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
        return START_STICKY;
    }

    @Override
    public void onCreate() {
        connect();
    }

    @Override
    public void onDestroy() {
        Log.i(TAG, "Shutting down Arduino service.");
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
