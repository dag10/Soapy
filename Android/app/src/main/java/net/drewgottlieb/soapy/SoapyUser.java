package net.drewgottlieb.soapy;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by drew on 7/5/15.
 */
public class SoapyUser {
    private String rfid;

    private String ldap = null;
    private String firstName = null;
    private String lastName = null;
    private String imageUrl = null;
    private String spotifyUsername = null;
    private String spotifyAccessToken = null;
    private ArrayList<SoapyPlaylist> playlists = null;
    private SoapyPlaylist playlist = null;
    private SoapyPlayback playback = null;

    public SoapyUser(String rfid) {
        this.rfid = rfid;
        playlists = new ArrayList<>();
    }

    public SoapyUser(String rfid, JSONObject data) throws JSONException {
        this(rfid);

        JSONObject jUser = data.getJSONObject("user");
        ldap = jUser.getString("ldap");
        firstName = jUser.getString("firstName");
        lastName = jUser.getString("lastName");

        if (jUser.has("playback")) {
            playback = new SoapyPlayback(jUser.getJSONObject("playback"));
        }

        if (jUser.has("spotifyAccount")) {
            JSONObject jSpotifyAccount = jUser.getJSONObject("spotifyAccount");
            imageUrl = jSpotifyAccount.getString("avatar");
            spotifyUsername = jSpotifyAccount.getString("username");
            spotifyAccessToken = jSpotifyAccount.getString("accessToken");
        }

        if (jUser.has("playlists")) {
            JSONArray jPlaylists = jUser.getJSONArray("playlists");
            for (int i = 0; i < jPlaylists.length(); i++) {
                playlists.add(new SoapyPlaylist(this, jPlaylists.getJSONObject(i)));
            }
        }

        if (jUser.has("selectedPlaylist")) {
            JSONObject jPlaylist = jUser.getJSONObject("selectedPlaylist");
            String uri = jPlaylist.getString("spotifyPlaylistUri");

            boolean hasPlaylist = false;
            for (SoapyPlaylist playlist : playlists) {
                if (playlist.getSpotifyPlaylist().getURI().equals(uri)) {
                    this.playlist = playlist;
                    hasPlaylist = true;
                    break;
                }
            }

            if (!hasPlaylist) {
                this.playlist = new SoapyPlaylist(this, jPlaylist);
                playlists.add(this.playlist);
            }
        }
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

    public String getFullName() {
        return getFirstName() + " " + getLastName();
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

    public SoapyPlaylist getPlaylist() {
        return playlist;
    }

    public List<SoapyTrack> getTracks() {
        if (playlist == null) {
            return new ArrayList<>();
        }

        return playlist.getTracks();
    }

    public SoapyPlayback getPlayback() {
        return playback;
    }
}
