package net.drewgottlieb.soapy;

import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.hardware.usb.UsbDeviceConnection;
import android.hardware.usb.UsbManager;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

import com.hoho.android.usbserial.driver.UsbSerialDriver;
import com.hoho.android.usbserial.driver.UsbSerialPort;
import com.hoho.android.usbserial.driver.UsbSerialProber;
import com.hoho.android.usbserial.util.SerialInputOutputManager;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;
import java.util.Timer;
import java.util.TimerTask;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

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

public class ArduinoService extends Service {
    public static final String RFID_INTENT = "new.drewgottlieb.RFID_INTENT";
    public static final String DOOR_INTENT = "new.drewgottlieb.DOOR_INTENT";

    private static String TAG = "ArduinoService";

    public class ArduinoBinder extends Binder {
        ArduinoService getService() {
            return ArduinoService.this;
        }
    }

    private boolean connected = false;
    private final ArduinoBinder mBinder = new ArduinoBinder();
    private List<Byte> buffer = new ArrayList<>();
    private boolean readingMessage = false;
    private boolean[] lampStatus = new boolean[2];

    private UsbSerialPort port;
    private final ExecutorService executorService = Executors.newSingleThreadExecutor();
    private SerialInputOutputManager serialIoManager;
    private final SerialInputOutputManager.Listener listener =
        new SerialInputOutputManager.Listener() {
        @Override
        public void onNewData(byte[] bytes) {
            onRead(bytes);
        }

        @Override
        public void onRunError(Exception e) {
            Log.w(TAG, "Arduino disconnected. Serial listener stopped due to error: " + e.getMessage());
            disconnect();
        }
    };

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

    protected void handleCompletedBuffer() {
        if (buffer == null || buffer.isEmpty()) {
            Log.e(TAG, "Can't handle empty message buffer.");
            return;
        }

        int size = buffer.size() - 1;
        byte[] bufArr = new byte[size];
        byte check = 0;
        for (int i = 0; i < size; i++) {
            bufArr[i] = buffer.get(i);
            check = (byte) (check ^ bufArr[i]);
        }

        // Verify checksum of data
        if (check != buffer.get(size)) {
            String failedString;
            try {
                failedString = new String(bufArr, "UTF-8");
            } catch (UnsupportedEncodingException e) {
                failedString = "<failed to decode UTF-8>";
            }
            Log.e(TAG, "Checksum mismatch! Sending poll request. (String: \"" + failedString + "\", Expected: " + buffer.get(size) + ", Computed: " + check);
            sendMessage("poll");
            return;
        }

        try {
            handleReceivedString(new String(bufArr, "UTF-8"));
        } catch (UnsupportedEncodingException e) {
            Log.e(TAG, "Failed to read arduino string.");
        }
    }

    public void onRead(byte[] data) {
        String debugMessage = "Read hex:  ";
        for (byte b : data) {
            debugMessage += String.format(" %02X", b);
        }
        Log.d(TAG, debugMessage);
        debugMessage = "Read ascii:";
        for (byte b : data) {
            if (b == '\n') {
                debugMessage += "\\n ";
            } else if (b == '\r') {
                debugMessage += "\\r ";
            } else {
                debugMessage += String.format(" %c ", b >= 32 && b <= 126 ? (char) b : '.');
            }
        }
        Log.d(TAG, debugMessage);

        for (int i = 0; i < data.length; i++) {
            byte b = data[i];

            if (b == 0x02) {
                buffer.clear();
                readingMessage = true;
            } else if (b == 0x03) {
                if (!readingMessage) {
                    continue;
                }

                handleCompletedBuffer();
                readingMessage = false;
            } else {
                buffer.add(b);
            }
        }
    }

    private void disconnect() {
        if (serialIoManager != null) {
            serialIoManager.stop();
            serialIoManager = null;
        }

        if (port != null) {
            try {
                port.close();
            } catch (IOException e) {
                // nothing
            }
            port = null;
        }

        connected = false;
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
        Log.i(TAG, "The arduino service was destroyed.");
        disconnect();
    }

    private void sendMessage(String str) {
        if (!connected) {
            Log.w(TAG,
                  "Tried to send message to Arduino but no connection found. (\"" + str + "\")");
            return;
        }

        Log.i(TAG, "Sending: \"" + str + "\"");
        str += "\n";

        byte[] buf = str.getBytes();

        try {
            port.write(buf, buf.length);
        } catch (IOException e) {
            Log.e(TAG, "Failed to write message to Arduino: " + e.getMessage());
        }
    }

    private void sendInitialPackets() {
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

    public void connect() {
        if (connected) {
            return;
        }

        UsbManager manager = (UsbManager) getSystemService(Context.USB_SERVICE);
        List<UsbSerialDriver> availableDrivers = UsbSerialProber.getDefaultProber().findAllDrivers(
                manager);

        if (availableDrivers.isEmpty()) {
            Log.w(TAG, "No available USB drivers found.");
            return;
        }

        UsbSerialDriver driver = availableDrivers.get(0);
        UsbDeviceConnection conn = manager.openDevice(driver.getDevice());
        if (conn == null) {
            Log.e(TAG, "Failed to open USB connection. Possible permissions issue.");
            return;
        }

        List<UsbSerialPort> ports = driver.getPorts();
        if (ports.isEmpty()) {
            Log.e(TAG, "USB driver has no ports.");
            conn.close();
            return;
        }

        port = ports.get(0);

        try {
            port.open(conn);
            port.setParameters(
                    9600,
                    UsbSerialPort.DATABITS_8,
                    UsbSerialPort.STOPBITS_1,
                    UsbSerialPort.PARITY_NONE);
        } catch (IOException e) {
            Log.e(TAG, "IOException when opening port with connection: " + e.getMessage());
            try {
                port.close();
            } catch (IOException e2) {
                // nothing
            }
            port = null;
            return;
        }

        Log.i(TAG, "Opened connection with serial device: " + port.getClass().getSimpleName());

        serialIoManager = new SerialInputOutputManager(port, listener);
        executorService.submit(serialIoManager);
        connected = true;

        // after 2 seconds, send initial messages to arduino.
        new Timer().schedule(new TimerTask() {
            @Override
            public void run() {
                sendInitialPackets();
            }
        }, 2000);
    }
}
