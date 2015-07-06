package net.drewgottlieb.soapy;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.widget.TextView;

import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.android.AndroidDeferredManager;


public class PlaylistSelectionActivity extends AppCompatActivity {
    public static final String EXTRA_RFID = "new.drewgottlieb.soapy.RFID";

    private SoapyUser user = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_playlist_selection);

        Intent intent = getIntent();
        String rfid = intent.getStringExtra(EXTRA_RFID);

        final TextView rfid_out = (TextView) findViewById(R.id.rfid_output);
        final PlaylistSelectionActivity activity = this;
        rfid_out.setText("Loading user with RFID " + rfid);

        DeferredManager dm = new AndroidDeferredManager();
        dm.when(SoapyUser.fetchUser(rfid))
        .done(new DoneCallback<SoapyUser>() {
            public void onDone(SoapyUser user) {
                activity.user = user;

                // TODO: Populate list here, not a textbox.
                String playlists = "Playlists for " + user.getFirstName() + " " + user.getLastName() + ":\n\n";
                for (SoapyPlaylist playlist : user.getPlaylists()) {
                    playlists += "- " + playlist.getName() + "\n";
                }
                rfid_out.setText(playlists);
            }
        })
        .fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
            public void onFail(SoapyWebAPI.SoapyWebError e) {
                rfid_out.setText("Failed to fetch user: " + e.getMessage());
            }
        });

    }
}
