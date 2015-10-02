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
    private ArrayList<SoapyTrack> tracks = null;
    private SoapyPlaylist playlist = null;

    public SoapyUser(String rfid) {
        this.rfid = rfid;
        playlists = new ArrayList<>();
        tracks = new ArrayList<>();
    }

    public SoapyUser(String rfid, JSONObject data) throws JSONException {
        this(rfid);

        JSONObject jUser = data.getJSONObject("user");
        ldap = jUser.getString("ldap");
        firstName = jUser.getString("first_name");
        lastName = jUser.getString("last_name");
        imageUrl = jUser.getString("avatar");
        spotifyUsername = jUser.getString("username");
        spotifyAccessToken = jUser.getString("access_token");

        if (data.has("playlists")) {
            JSONArray jPlaylists = data.getJSONArray("playlists");
            for (int i = 0; i < jPlaylists.length(); i++) {
                playlists.add(new SoapyPlaylist(jPlaylists.getJSONObject(i)));
            }
        }

        if (data.has("playlist")) {
            JSONObject jPlaylist = data.getJSONObject("playlist");
            String uri = jPlaylist.getString("uri");

            for (SoapyPlaylist playlist : playlists) {
                if (playlist.getURI().equals(uri)) {
                    this.playlist = playlist;
                    break;
                }
            }
        }

        if (data.has("tracks")) {
            JSONArray jTracks = data.getJSONArray("tracks");
            for (int i = 0; i < jTracks.length(); i++) {
                tracks.add(new SoapyTrack(jTracks.getJSONObject(i)));
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
        return tracks;
    }
}
