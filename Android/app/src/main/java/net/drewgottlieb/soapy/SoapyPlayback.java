package net.drewgottlieb.soapy;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by drew on 2/13/16.
 */
public class SoapyPlayback {
    public enum PlaybackMode {
        SHUFFLE,
        LINEAR,
    }

    private PlaybackMode playbackMode = null;

    public SoapyPlayback(JSONObject data) throws JSONException {
        playbackMode = PlaybackMode.valueOf(data.getString("playbackMode"));
    }

    public PlaybackMode getPlaybackMode() {
        return playbackMode;
    }
}
