package net.drewgottlieb.soapy;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;

import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.android.AndroidDeferredManager;

import java.util.ArrayList;
import java.util.List;
import java.util.Random;


public class PlaylistSelectionActivity extends SoapyActivity {
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

        final ListView playlist_listview = (ListView) findViewById(R.id.playlist_listview);
        final List<SoapyPlaylist> playlists = new ArrayList<SoapyPlaylist>();
        PlaylistArrayAdapter playlist_adapter = new PlaylistArrayAdapter(this, android.R.layout.simple_list_item_1, playlists);
        playlist_listview.setAdapter(playlist_adapter);
        playlist_listview.setDivider(null);
        playlist_listview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, final View view, final int position, long id) {
                SoapyPlaylist playlist = playlists.get(position);
                rfid_out.setText(playlist.getName());
            }
        });

        DeferredManager dm = new AndroidDeferredManager();
        dm.when(SoapyWebAPI.getInstance().fetchUserAndPlaylists(rfid)).done(new DoneCallback<SoapyUser>() {
            public void onDone(SoapyUser user) {
                activity.user = user;

                List<SoapyPlaylist> fetchedPlaylists = user.getPlaylists();
                for (SoapyPlaylist playlist : fetchedPlaylists) {
                    playlists.add(playlist);
                }

                String playlistsStr = "\n\nChoose a playlist";
                rfid_out.setText(playlistsStr);
            }
        }).fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
            public void onFail(SoapyWebAPI.SoapyWebError e) {
                e.printStackTrace();
                rfid_out.setText("Failed to fetch user: " + e.getMessage());
            }
        });

    }
}

class PlaylistArrayAdapter extends ArrayAdapter<SoapyPlaylist> {
    public PlaylistArrayAdapter(Context context, int textViewResourceId, List<SoapyPlaylist> objects) {
        super(context, textViewResourceId, objects);
    }

    @Override
    public long getItemId(int position) {
        SoapyPlaylist item = getItem(position);
        return item.getURI().hashCode();
    }

    @Override
    public boolean hasStableIds() {
        return true;
    }

    @Override
    public View getView(final int position, View convertView, ViewGroup parent) {
        if (convertView == null) {
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.entry_playlist, parent, false);
        }

        SoapyPlaylist playlist = getItem(position);

        TextView fragTextView = (TextView) convertView.findViewById(R.id.playlist_fragment_text);
        fragTextView.setText(playlist.getName());

        TextView fragSongCountView = (TextView) convertView.findViewById(R.id.playlist_fragment_songcount_text);
        int totalTracks = playlist.getTotalTracks();
        fragSongCountView.setText(totalTracks + " song" + (totalTracks == 1 ? "" : "s"));

        // TODO: Load actual album art.
        ImageView albumArt = (ImageView) convertView.findViewById(R.id.playlist_fragment_image);
        Random random = new Random();
        int mix = Color.HSVToColor(new float[]{random.nextFloat(), 1.f, 0.5f});
        int red = (random.nextInt(256) + Color.red(mix)) / 2;
        int green = (random.nextInt(256) + Color.green(mix)) / 2;
        int blue = (random.nextInt(256) + Color.blue(mix)) / 2;
        albumArt.setBackgroundColor(Color.rgb(red, green, blue));

        return convertView;
    }
}
