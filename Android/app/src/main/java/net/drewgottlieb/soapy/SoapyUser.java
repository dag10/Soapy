package net.drewgottlieb.soapy;

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

import java.util.ArrayList;
import java.util.List;

/**
 * Created by drew on 7/5/15.
 */
public class SoapyUser {
    private SoapyWebAPI api;
    private String rfid;

    private String ldap = null;
    private String firstName = null;
    private String lastName = null;
    private String imageUrl = null;
    private String spotifyUsername = null;
    private String spotifyAccessToken = null;
    private ArrayList<SoapyPlaylist> playlists = null;

    public SoapyUser(SoapyWebAPI api, String rfid) {
        this.api = api;
        this.rfid = rfid;
        playlists = new ArrayList<SoapyPlaylist>();
    }

    public String getRfid() {
        return this.rfid;
    }

    public String getLdap() {
        return this.ldap;
    }

    public String getFirstName() {
        return this.firstName;
    }

    public String getLastName() {
        return this.lastName;
    }

    public String getImageUrl() {
        return this.imageUrl;
    }

    public String getSpotifyUsername() {
        return this.spotifyUsername;
    }

    public String getSpotifyAccessToken() {
        return this.spotifyAccessToken;
    }

    public List<SoapyPlaylist> getPlaylists() {
        return playlists;
    }

    public static Promise<SoapyUser, SoapyWebAPI.SoapyWebError, Void> fetchUser(String rfid) {
        final Deferred<SoapyUser, SoapyWebAPI.SoapyWebError, Void> deferred = new DeferredObject<SoapyUser, SoapyWebAPI.SoapyWebError, Void>();
        final String rfid_id = rfid;

        SoapyWebAPI api = SoapyWebAPI.getInstance();
        final SoapyUser user = new SoapyUser(api, rfid_id);

        DeferredManager dm = new DefaultDeferredManager();
        dm.when(api.get("api/rfid/" + rfid_id + "/playlists"))
        .done(new DoneCallback<JSONObject>() {
            public void onDone(JSONObject obj) {
                try {
                    JSONObject jUser = obj.getJSONObject("user");
                    user.ldap = jUser.getString("ldap");
                    user.firstName = jUser.getString("first_name");
                    user.lastName = jUser.getString("last_name");
                    user.imageUrl = jUser.getString("avatar");
                    user.spotifyUsername = jUser.getString("username");
                    user.spotifyAccessToken = jUser.getString("access_token");

                    JSONArray jPlaylists = obj.getJSONArray("playlists");
                    for (int i = 0; i < jPlaylists.length(); i++) {
                        user.playlists.add(new SoapyPlaylist(jPlaylists.getJSONObject(i)));
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    deferred.reject(new SoapyWebAPI.SoapyWebError("Failed to parse playlist JSON: " + e.getMessage()));
                }

                deferred.resolve(user);
            }
        })
        .fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
            public void onFail(SoapyWebAPI.SoapyWebError e) {
                deferred.reject(e);
            }
        });

        return deferred.promise();
    }
}
