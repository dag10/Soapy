package net.drewgottlieb.soapy;

import android.content.Context;
import android.content.SharedPreferences;

/**
 * Created by drew on 9/13/15.
 */
public class SoapyPreferences implements SharedPreferences.OnSharedPreferenceChangeListener {
    private static SoapyPreferences instance = null;
    private SharedPreferences preferences;

    private String spotifyClientId = null;
    private String soapySecret = null;
    private String soapyUrl = null;
    private Boolean enableVolumeShortcut = null;

    protected static final String SPOTIFY_CLIENT_ID = "spotifyClientId";
    protected static final String SOAPY_SECRET = "soapySecret";
    protected static final String SOAPY_URL = "soapyUrl";
    protected static final String SOAPY_ENABLE_VOLUME_SHORTCUT = "enableVolumeShortcut";

    public static SoapyPreferences createInstance(Context context) {
        if (instance == null) {
            instance = new SoapyPreferences(context);
        }

        return instance;
    }

    public static SoapyPreferences getInstance() {
        return instance;
    }

    protected SoapyPreferences(Context context) {
        preferences = context.getSharedPreferences("Soapy", Context.MODE_PRIVATE);
        preferences.registerOnSharedPreferenceChangeListener(this);
    }

    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) {
        switch (key) {
            case SPOTIFY_CLIENT_ID:
                refreshSpotifyClientId();
                break;
            case SOAPY_SECRET:
                refreshSoapySecret();
                break;
            case SOAPY_URL:
                refreshSoapyUrl();
                break;
            case SOAPY_ENABLE_VOLUME_SHORTCUT:
                refreshEnableVolumeShortcut();
                break;
        }
    }

    protected void refreshSpotifyClientId() {
        // TODO: Don't hard-code spotify client ID.
        spotifyClientId = preferences.getString(SPOTIFY_CLIENT_ID, "1cc17e3b20364be7910428b1d8c534ed");
    }

    protected void refreshSoapySecret() {
        soapySecret = preferences.getString(SOAPY_SECRET, "clicks123");
    }

    protected void refreshSoapyUrl() {
        soapyUrl = preferences.getString(SOAPY_URL, "https://soapy-api.csh.rit.edu/");
    }

    protected void refreshEnableVolumeShortcut() {
        enableVolumeShortcut = new Boolean(preferences.getBoolean(SOAPY_ENABLE_VOLUME_SHORTCUT, false));
    }

    public String getSpotifyClientId() {
        if (spotifyClientId == null) {
            refreshSpotifyClientId();
        }

        return spotifyClientId;
    }

    public String getSoapySecret() {
        if (soapySecret == null) {
            refreshSoapySecret();
        }

        return soapySecret;
    }

    public String getSoapyUrl() {
        if (soapyUrl == null) {
            refreshSoapyUrl();
        }

        return soapyUrl;
    }

    public boolean getEnableVolumeShortcut() {
        if (enableVolumeShortcut == null) {
            refreshEnableVolumeShortcut();
        }

        return enableVolumeShortcut;
    }
}
