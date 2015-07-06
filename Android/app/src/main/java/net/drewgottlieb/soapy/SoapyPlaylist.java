package net.drewgottlieb.soapy;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by drew on 7/5/15.
 */
public class SoapyPlaylist {
    private String name = null;
    private String uri = null;
    private String imageUrl = null;

    public SoapyPlaylist(JSONObject jPlaylist) throws JSONException {
        name = jPlaylist.getString("name");
        uri = jPlaylist.getString("uri");

        if (jPlaylist.has("images")) {
            JSONArray jImages = jPlaylist.getJSONArray("images");
            if (jImages.length() > 0) {
                JSONObject jImage = jImages.getJSONObject(0);
                imageUrl = jImage.getString("url");
            }
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
}
