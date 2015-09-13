package net.drewgottlieb.soapy;

import android.util.Log;

import org.apache.http.client.HttpClient;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.jdeferred.Deferred;
import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.Promise;
import org.jdeferred.impl.DefaultDeferredManager;
import org.jdeferred.impl.DeferredObject;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.EOFException;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * Created by drew on 7/5/15.
 */
public class SoapyWebAPI {
    private static final ExecutorService executorService = Executors.newCachedThreadPool();
    private static final DeferredManager dm = new DefaultDeferredManager(executorService);

    public static class SoapyWebError extends Exception {
        public SoapyWebError(String message) {
            super(message);
        }
    }

    private String host;
    private int port;
    private boolean secure;

    private static SoapyWebAPI instance = null;

    public static SoapyWebAPI getInstance() {
        if (instance == null) {
            // TODO: Load from a config, don't hard code.
            instance = new SoapyWebAPI("soapy.csh.rit.edu", 80, false);
        }

        return instance;
    }

    protected SoapyWebAPI(String host, int port, boolean secure) {
        this.host = host;
        this.port = port;
        this.secure = secure;
    }

    public Promise<JSONObject, SoapyWebError, Void> get(String route) {
        final Deferred<JSONObject, SoapyWebError, Void> deferred = new DeferredObject<JSONObject, SoapyWebError, Void>();

        String protocol = secure ? "https://" : "http://";
        URL url = null;
        try {
            url = new URL(protocol + host + ":" + port + "/" + route);
        } catch (MalformedURLException e) {
            e.printStackTrace();
            deferred.reject(new SoapyWebError("Malformed URL: " + url));
            return deferred.promise();
        }
        final URL fURL = url;

        (new Thread() {
            public void run() {
                HttpURLConnection conn = null;
                String result = null;
                try {
                    Log.i("WebAPI", fURL.toExternalForm()); // TODO TMP
                    conn = (HttpURLConnection) fURL.openConnection();
                    BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream(), "UTF-8"), 8);
                    StringBuilder sb = new StringBuilder();

                    String line = null;
                    while ((line = in.readLine()) != null) {
                        sb.append(line + "\n");
                    }

                    result = sb.toString();
                } catch (EOFException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("Empty response from server."));
                    return;
                } catch (IOException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("IOException: " + e.getMessage()));
                    return;
                } finally {
                    if (conn != null) {
                        conn.disconnect();
                    }
                }

                JSONObject obj = null;
                try {
                    obj = new JSONObject(result);
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("JSONException: " + e.getMessage()));
                    return;
                }

                if (obj.has("error")) {
                    try {
                        deferred.reject(new SoapyWebError(obj.getString("error")));
                    } catch (JSONException e) {
                        e.printStackTrace();
                        deferred.reject(new SoapyWebError("Failed to read response error."));
                    }
                    return;
                }

                deferred.resolve(obj);
            }
        }).start();

        return deferred.promise();
    }

    public Promise<SoapyUser, SoapyWebError, Void> fetchUserAndPlaylists(String rfid) {
        return fetchUser(rfid, "playlists");
    }

    public Promise<SoapyUser, SoapyWebError, Void> fetchUserAndTracks(String rfid) {
        return fetchUser(rfid, "tracks");
    }

    /**
     * @param rfid
     * @param request either "playlists" or "tracks"
     */
    protected Promise<SoapyUser, SoapyWebError, Void> fetchUser(String rfid, String request) {
        final Deferred<SoapyUser, SoapyWebError, Void> deferred = new DeferredObject<>();
        final String rfid_id = rfid;

        dm.when(get("api/rfid/" + rfid_id + "/" + request)).done(new DoneCallback<JSONObject>() {
            public void onDone(JSONObject obj) {
                try {
                    if (obj.has("error")) {
                        String errorMsg = obj.getString("error");
                        deferred.reject(new SoapyWebError("Remote error: " + errorMsg));
                        return;
                    }

                    SoapyUser user = new SoapyUser(rfid_id, obj);
                    deferred.resolve(user);
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("Failed to parse playlist JSON: " + e.getMessage()));
                }
            }
        }).fail(new FailCallback<SoapyWebError>() {
            public void onFail(SoapyWebAPI.SoapyWebError e) {
                deferred.reject(e);
            }
        });

        return deferred.promise();
    }
}
