package net.drewgottlieb.soapy;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.util.Log;
import android.view.ContextThemeWrapper;
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

import java.io.InputStream;
import java.util.ArrayList;
import java.util.List;
import java.util.Random;


public class PlaylistSelectionActivity extends SoapyActivity {
    public static final String EXTRA_RFID = "new.drewgottlieb.soapy.RFID";

    private SoapyUser user = null;
    private String rfid = null;
    private SoapyPlaylist selectedPlaylist = null;
    private ListView playlistListview = null;
    private PlaylistArrayAdapter playlistAdapter = null;

    private void setSelectedPlaylist(SoapyPlaylist playlist) {
        selectedPlaylist = playlist;
        playlistAdapter.setSelectedPlaylist(playlist);

        if (playlistListview != null && playlistAdapter != null) {
            final int idx = playlist == null ? -1 : playlistAdapter.getPositionForPlaylist(playlist);

            playlistListview.post(new Runnable() {
                @Override
                public void run() {
                    int firstListItemPosition = playlistListview.getFirstVisiblePosition();
                    int lastListItemPosition = firstListItemPosition + (int) (playlistListview.getChildCount() * 0.7);
                    Log.i(TAG, "Visible Item Range: " + firstListItemPosition + " to " + lastListItemPosition);
                    if (idx >= 0 && (idx < firstListItemPosition || idx > lastListItemPosition)) {
                        int topMargin = (playlistListview.getHeight() / 2) -
                                (playlistListview.getChildAt(0).getHeight() / 2);
                        Log.i(TAG, "Scrolling to position " + idx + " with offset " + topMargin);
                        playlistListview.smoothScrollToPositionFromTop(idx, topMargin);
                    }
                }
            });
        }

        TextView rfid_out = (TextView) findViewById(R.id.rfid_output);
        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);

        if (playlist == null) {
            rfid_out.setText("\n\nChoose a playlist");
            fab.hide();
        } else {
            rfid_out.setText(playlist.getName());
            fab.show();
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_playlist_selection);

        final FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setImageDrawable(new SoapyFab.FabTextDrawable("GO", 1.f, 16.f));

        Intent intent = getIntent();
        rfid = intent.getStringExtra(EXTRA_RFID);

        final TextView rfid_out = (TextView) findViewById(R.id.rfid_output);
        final PlaylistSelectionActivity activity = this;
        rfid_out.setText("Loading user with RFID " + rfid);

        playlistListview = (ListView) findViewById(R.id.playlist_listview);
        final List<SoapyPlaylist> playlists = new ArrayList<>();
        playlistAdapter = new PlaylistArrayAdapter(this, android.R.layout.simple_list_item_1, playlists);
        playlistListview.setAdapter(playlistAdapter);
        playlistListview.setDivider(null);

        playlistListview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, final View view, final int position, long id) {
                setSelectedPlaylist((SoapyPlaylist) playlistListview.getItemAtPosition(position));
            }
        });

        fab.hide();

        final DeferredManager adm = new AndroidDeferredManager();
        adm.when(SoapyWebAPI.getInstance().fetchUserAndPlaylists(rfid)).done(new DoneCallback<SoapyUser>() {
            public void onDone(SoapyUser user) {
                PlaylistSelectionActivity.this.user = user;

                List<SoapyPlaylist> fetchedPlaylists = user.getPlaylists();
                for (SoapyPlaylist playlist : fetchedPlaylists) {
                    playlists.add(playlist);
                }

                fab.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (selectedPlaylist == null) {
                            Log.w(TAG, "Go tapped, but no playlist selected.");
                            return;
                        }

                        fab.setEnabled(false);
                        fab.setAlpha(0.8f);
                        playlistListview.setEnabled(false);

                        adm.when(SoapyWebAPI.getInstance().setSelectedPlaylist(
                                rfid, selectedPlaylist.getURI())).done(new DoneCallback<Void>() {
                            @Override
                            public void onDone(Void result) {
                                PlaylistSelectionActivity.this.finish();
                            }
                        }).fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
                            @Override
                            public void onFail(SoapyWebAPI.SoapyWebError result) {
                                new AlertDialog.Builder(
                                        new ContextThemeWrapper(
                                                PlaylistSelectionActivity.this,
                                                R.style.SoapyDialog))
                                        .setTitle("Failed to save selection")
                                        .setMessage(result.getMessage())
                                        .setPositiveButton("Dismiss", new DialogInterface.OnClickListener() {
                                            @Override
                                            public void onClick(DialogInterface dialog, int which) {
                                                fab.setEnabled(true);
                                                fab.setAlpha(1.f);
                                                playlistListview.setEnabled(true);
                                            }
                                        })
                                        .create().show();
                                Log.e(TAG, "Failed to set playlist. " + result.getMessage());
                            }
                        });
                    }
                });

                setSelectedPlaylist(user.getPlaylist());
            }
        }).fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
            public void onFail(SoapyWebAPI.SoapyWebError e) {
                e.printStackTrace();
                rfid_out.setText("Failed to fetch user: " + e.getMessage());
            }
        });
    }
}

class DownloadImageTask extends AsyncTask<String, Void, Bitmap> {
    ImageView bmImage;

    public DownloadImageTask(ImageView bmImage) {
        this.bmImage = bmImage;
    }

    protected Bitmap doInBackground(String... urls) {
        String urldisplay = urls[0];
        Bitmap mIcon11 = null;
        try {
            InputStream in = new java.net.URL(urldisplay).openStream();
            mIcon11 = BitmapFactory.decodeStream(in);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return mIcon11;
    }

    protected void onPostExecute(Bitmap result) {
        bmImage.setImageBitmap(result);
    }
}

class PlaylistArrayAdapter extends ArrayAdapter<SoapyPlaylist> {
    private SoapyPlaylist selectedPlaylist = null;

    public PlaylistArrayAdapter(Context context, int textViewResourceId, List<SoapyPlaylist> objects) {
        super(context, textViewResourceId, objects);
    }

    public void setSelectedPlaylist(SoapyPlaylist playlist) {
        selectedPlaylist = playlist;
        notifyDataSetChanged();
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

    public int getPositionForPlaylist(SoapyPlaylist playlist) {
        String uri = playlist.getURI();
        for (int i = 0; i < getCount(); i++) {
            if (uri.equals(getItem(i).getURI())) {
                return i;
            }
        }

        return -1;
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
        Random random = new Random(playlist.getURI().hashCode());
        int mix = Color.HSVToColor(new float[]{random.nextFloat(), 1.f, 0.5f});
        int red = (random.nextInt(256) + Color.red(mix)) / 2;
        int green = (random.nextInt(256) + Color.green(mix)) / 2;
        int blue = (random.nextInt(256) + Color.blue(mix)) / 2;
        albumArt.setBackgroundColor(Color.rgb(red, green, blue));

        if (selectedPlaylist != null && playlist.getURI().equals(selectedPlaylist.getURI())) {
            convertView.setBackgroundColor(
                    convertView.getResources().getColor(R.color.PLAYLIST_SELECTED_BG));
        } else {
            convertView.setBackgroundColor(
                    convertView.getResources().getColor(R.color.TRANSPARENT));
        }

        new DownloadImageTask(albumArt).execute(playlist.getImageURL());

        return convertView;
    }
}
