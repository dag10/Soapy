package net.drewgottlieb.soapy;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;


public class SoapyPlaylist {
    private int soapyPlaylistId;
    private String lastPlayedSongUri;
    private String spotifyPlaylistUri;
    private SoapyUser listener;
    private SpotifyPlaylist playlist;
    private List<SoapyTrack> tracks = new ArrayList<>();

    public SoapyPlaylist(SoapyUser listener, JSONObject jObject) throws JSONException {
        this.listener = listener;
        soapyPlaylistId = jObject.getInt("soapyPlaylistId");

        if (jObject.has("spotifyPlaylistUri")) {
            spotifyPlaylistUri = jObject.getString("spotifyPlaylistUri");
        }

        if (jObject.has("lastPlayedSongUri")) {
            lastPlayedSongUri = jObject.getString("lastPlayedSongUri");
        }

        if (jObject.has("spotifyPlaylist")) {
            this.playlist = new SpotifyPlaylist(jObject.getJSONObject("spotifyPlaylist"));

            if (spotifyPlaylistUri == null) {
                spotifyPlaylistUri = playlist.getURI();
            }
        }

        if (jObject.has("tracklist")) {
            JSONArray jTrackList = jObject.getJSONArray("tracklist");
            for (int i = 0; i < jTrackList.length(); i++) {
                tracks.add(new SoapyTrack(jTrackList.getJSONObject(i)));
            }
        }
    }

    public SoapyPlaylist(SoapyUser listener, SpotifyPlaylist playlist) {
        this.listener = listener;
        this.playlist = playlist;
    }

    public String getSpotifyPlaylistUri() {
        return spotifyPlaylistUri;
    }

    public String getLastPlayedSongUri() {
        return lastPlayedSongUri;
    }

    public int getSoapyPlaylistId() {
        return soapyPlaylistId;
    }

    public SoapyUser getListener() {
        return listener;
    }

    public SpotifyPlaylist getSpotifyPlaylist() {
        return playlist;
    }

    public List<SoapyTrack> getTracks() {
        return tracks;
    }
}
