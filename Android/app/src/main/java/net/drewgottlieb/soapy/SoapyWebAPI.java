package net.drewgottlieb.soapy;

import android.util.Log;

import com.squareup.okhttp.ConnectionSpec;
import com.squareup.okhttp.FormEncodingBuilder;
import com.squareup.okhttp.MediaType;
import com.squareup.okhttp.OkHttpClient;
import com.squareup.okhttp.Request;
import com.squareup.okhttp.RequestBody;
import com.squareup.okhttp.Response;
import com.squareup.okhttp.TlsVersion;

import org.jdeferred.Deferred;
import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.Promise;
import org.jdeferred.impl.DefaultDeferredManager;
import org.jdeferred.impl.DeferredObject;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import javax.net.ssl.HttpsURLConnection;

/**
 * Created by drew on 7/5/15.
 */
public class SoapyWebAPI {
    private static String TAG = "SoapyWebAPI";
    private static final ExecutorService executorService = Executors.newCachedThreadPool();
    private static final DeferredManager dm = new DefaultDeferredManager(executorService);
    private static final MediaType MEDIA_TYPE_JSON = MediaType.parse(
            "application/json; charset=utf-8");

    public static class SoapyWebError extends Exception {
        public SoapyWebError(String message) {
            super(message);
        }
    }

    protected enum RequestType {
        GET,
        POST
    }

    private static SoapyWebAPI instance = null;
    private SoapyPreferences preferences = null;
    private OkHttpClient client = new OkHttpClient();

    public static SoapyWebAPI getInstance() {
        if (instance == null) {
            instance = new SoapyWebAPI();
        }

        return instance;
    }

    protected SoapyWebAPI() {
        preferences = SoapyPreferences.getInstance();

        // Only allow TLSv1.0 because CSH servers don't support SSLv3, and newer TLS doesn't seem to work.
        ConnectionSpec spec = new ConnectionSpec.Builder(ConnectionSpec.MODERN_TLS)
                .tlsVersions(TlsVersion.TLS_1_0)
                .build();
        client.setConnectionSpecs(Collections.singletonList(spec));
    }

    public Promise<JSONObject, SoapyWebError, Void> get(String route) {
        return request(RequestType.GET, route, (RequestBody) null);
    }

    public Promise<JSONObject, SoapyWebError, Void> post(String route, Map<String, String> vars) {
        return request(RequestType.POST, route, vars);
    }

    public Promise<JSONObject, SoapyWebError, Void> post(String route, String body) {
        return request(RequestType.POST, route, body);
    }

    protected Promise<JSONObject, SoapyWebError, Void> request(final RequestType type, String route, final Map<String, String> vars) {
        RequestBody rBody = null;

        if (type == RequestType.POST) {
            FormEncodingBuilder bodyBuilder = new FormEncodingBuilder();

            if (vars != null) {
                for (String key : vars.keySet()) {
                    bodyBuilder.add(key, vars.get(key));
                }
            }

            rBody = bodyBuilder.build();
        }

        return request(type, route, rBody);
    }

    protected Promise<JSONObject, SoapyWebError, Void> request(final RequestType type, String route, final String body) {
        RequestBody rBody = null;

        if (type == RequestType.POST) {
            rBody = RequestBody.create(MEDIA_TYPE_JSON, body);
        }

        return request(type, route, rBody);
    }

    protected Promise<JSONObject, SoapyWebError, Void> request(final RequestType type, String route, final RequestBody body) {
        final Deferred<JSONObject, SoapyWebError, Void> deferred = new DeferredObject<>();

        URL url = null;
        try {
            url = new URL(preferences.getSoapyUrl() + route);
        } catch (MalformedURLException e) {
            e.printStackTrace();
            deferred.reject(new SoapyWebError("Malformed URL: " + url));
            return deferred.promise();
        }
        final URL fURL = url;

        Log.i(TAG, "Making " + type.toString() + " request to " + url.toExternalForm());

        executorService.submit(new Runnable() {
            public void run() {
                String result = null;
                try {
                    Request.Builder builder = new Request.Builder()
                            .url(fURL)
                            .header("X-Soapy-Secret", preferences.getSoapySecret());

                    if (type == RequestType.POST) {
                        builder.method("POST", body);
                    }

                    Response response = client.newCall(builder.build()).execute();
                    result = response.body().string();
                } catch (IOException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("IOException: " + e.getMessage()));
                    return;
                }

                JSONObject obj = null;
                try {
                    obj = new JSONObject(result);
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("JSONException: " + e.getMessage()));
                    Log.w(TAG, "Received JSON was:\n" + result);
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
        });

        return deferred.promise();
    }

    public Promise<Void, SoapyWebError, Void> setSelectedPlaylist(final String rfid, final String playlistUri) {
        final Deferred<Void, SoapyWebError, Void> deferred = new DeferredObject<>();

        HashMap<String, String> vars = new HashMap<>();
        vars.put("playlist_uri", playlistUri);

        dm.when(post("api/rfid/" + rfid + "/playlist/set", vars)).done(new DoneCallback<JSONObject>() {
            @Override
            public void onDone(JSONObject result) {
                try {
                    if (result.has("error")) {
                        String errorMsg = result.getString("error");
                        deferred.reject(new SoapyWebError("Remote error: " + errorMsg));
                        return;
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("Failed to parse response JSON: " + e.getMessage()));
                }

                deferred.resolve(null);
            }
        }).fail(new FailCallback<SoapyWebError>() {
            @Override
            public void onFail(SoapyWebError result) {
                deferred.reject(result);
            }
        });

        return deferred.promise();
    }

    public Promise<Void, SoapyWebError, Void> setLastSongPlayed(final String rfid, final String songUri) {
        final Deferred<Void, SoapyWebError, Void> deferred = new DeferredObject<>();

        HashMap<String, String> vars = new HashMap<>();
        vars.put("song_uri", songUri);

        dm.when(post("api/rfid/" + rfid + "/song/playing", vars)).done(new DoneCallback<JSONObject>() {
            @Override
            public void onDone(JSONObject result) {
                try {
                    if (result.has("error")) {
                        String errorMsg = result.getString("error");
                        deferred.reject(new SoapyWebError("Remote error: " + errorMsg));
                        return;
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("Failed to parse response JSON: " + e.getMessage()));
                }

                deferred.resolve(null);
            }
        }).fail(new FailCallback<SoapyWebError>() {
            @Override
            public void onFail(SoapyWebError result) {
                deferred.reject(result);
            }
        });

        return deferred.promise();
    }

    public Promise<Void, Throwable, Void> uploadLogs(final List<LogService.LogEvent> events) {
        final Deferred<Void, Throwable, Void> deferred = new DeferredObject<>();

        String json = null;

        try {
            JSONObject jObj = new JSONObject();
            jObj.put("bathroom", preferences.getBathroomName());

            JSONArray jEvents = new JSONArray();
            for (LogService.LogEvent event : events) {
                JSONObject jEvent = new JSONObject();
                jEvent.put("level",  event.getLevel().toString());
                jEvent.put("time", event.getDate().toString());
                jEvent.put("tag", event.getTag());
                jEvent.put("message", event.getMessage());
                jEvents.put(jEvent);
            }

            jObj.put("events", jEvents);
            json = jObj.toString();
        } catch (JSONException e) {
            deferred.reject(e);
            return deferred.promise();
        }

        dm.when(post("api/log/add", json)).done(new DoneCallback<JSONObject>() {
            @Override
            public void onDone(JSONObject result) {
                try {
                    if (result.has("error")) {
                        String errorMsg = result.getString("error");
                        deferred.reject(new SoapyWebError("Remote error: " + errorMsg));
                        return;
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebError("Failed to parse response JSON: " + e.getMessage()));
                }

                deferred.resolve(null);
            }
        }).fail(new FailCallback<SoapyWebError>() {
            @Override
            public void onFail(SoapyWebError result) {
                deferred.reject(result);
            }
        });

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
    protected Promise<SoapyUser, SoapyWebError, Void> fetchUser(final String rfid, String request) {
        final Deferred<SoapyUser, SoapyWebError, Void> deferred = new DeferredObject<>();

        dm.when(get("api/rfid/" + rfid + "/" + request)).done(new DoneCallback<JSONObject>() {
            public void onDone(JSONObject obj) {
                try {
                    if (obj.has("error")) {
                        String errorMsg = obj.getString("error");
                        deferred.reject(new SoapyWebError("Remote error: " + errorMsg));
                        return;
                    }

                    SoapyUser user = new SoapyUser(rfid, obj);
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
