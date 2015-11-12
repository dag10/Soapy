package net.drewgottlieb.soapy;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by drew on 7/5/15.
 */
public class SpotifyPlaylist {
    private String name = null;
    private String uri = null;
    private String imageUrl = null;
    private String lastPlayedSong = null;
    private int totalTracks = 0;

    public SpotifyPlaylist(JSONObject jPlaylist) throws JSONException {
        uri = jPlaylist.getString("uri");

        if (jPlaylist.has("name")) {
            name = jPlaylist.getString("name");
        }

        if (jPlaylist.has("images")) {
            JSONArray jImages = jPlaylist.getJSONArray("images");
            if (jImages.length() > 0) {
                JSONObject jSmallestImage = jImages.getJSONObject(0);
                int smallestHeight = jSmallestImage.getInt("height");
                for (int i = 1; i < jImages.length(); i++) {
                    JSONObject jImage = jImages.getJSONObject(i);
                    int height = jImage.getInt("height");
                    if (height < smallestHeight) {
                        jSmallestImage = jImage;
                        smallestHeight = height;
                    }
                }
                imageUrl = jSmallestImage.getString("url");
            }
        }

        if (jPlaylist.has("lastPlayedSong")) {
            lastPlayedSong = jPlaylist.getString("lastPlayedSong");
        }

        if (jPlaylist.has("tracks")) {
            JSONObject jTracks = jPlaylist.getJSONObject("tracks");
            totalTracks = jTracks.getInt("total");
        }
    }

    public String getName() {
        return this.name;
    }

    @Override
    public String toString() {
        return this.getName() + " (" + this.getURI() + ")";
    }

    public String getURI() {
        return this.uri;
    }

    public String getImageURL() {
        return this.imageUrl;
    }

    public int getTotalTracks() {
        return this.totalTracks;
    }

    public void setLastPlayedSong(String uri) {
        lastPlayedSong = uri;
    }

    public String getLastPlayedSong() {
        return lastPlayedSong;
    }
}
