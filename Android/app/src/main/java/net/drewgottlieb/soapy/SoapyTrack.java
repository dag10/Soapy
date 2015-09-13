package net.drewgottlieb.soapy;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by drew on 9/11/15.
 */
public class SoapyTrack {
    private boolean local = false;
    private String title = null;
    private String artist = null;
    private String album = null;
    private String uri = null;
    private String imageUrl = null;
    private int duration_ms = 0;

    public SoapyTrack(JSONObject jTrack) throws JSONException {
        JSONObject jAlbum = jTrack.getJSONObject("album");
        JSONArray jArtists = jTrack.getJSONArray("artists");
        JSONArray jImages = jAlbum.getJSONArray("images");

        if (jImages.length() > 0) {
            JSONObject jImage = jImages.getJSONObject(0);
            imageUrl = jImage.getString("url");
        }

        local = jTrack.getBoolean("is_local");
        title = jTrack.getString("name");
        artist = jArtists.getJSONObject(0).getString("name");
        album = jAlbum.getString("name");
        duration_ms = jTrack.getInt("duration_ms");
        uri = jTrack.getString("uri");
    }

    public String getTitle() {
        return title;
    }

    public String getAlbum() {
        return album;
    }

    public String getArtist() {
        return artist;
    }

    @Override
    public String toString() {
        return this.getTitle() + " by " + this.getArtist() + " (" + this.getURI() + ")";
    }

    public String getURI() {
        return this.uri;
    }

    public String getImageURL() {
        return this.imageUrl;
    }

    public int getDurationMs() {
        return this.duration_ms;
    }

    public boolean isLocal() {
        return this.local;
    }
}

