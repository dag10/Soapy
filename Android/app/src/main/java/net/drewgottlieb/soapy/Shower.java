package net.drewgottlieb.soapy;

import android.util.Log;

import org.jdeferred.Deferred;

import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.Promise;
import org.jdeferred.impl.DefaultDeferredManager;
import org.jdeferred.impl.DeferredObject;

import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class Shower {
    private String rfid = null;
    private SoapyUser user = null;
    private List<SoapyTrack> tracks = null;
    private int nextTrackIndex = 0;

    private static final ExecutorService executorService = Executors.newCachedThreadPool();
    private static final DeferredManager dm = new DefaultDeferredManager(executorService);

    public Shower() {
    }

    public boolean isPlayable() {
        return (rfid != null);
    }

    public void setRfid(String rfid) {
        this.rfid = rfid;
    }

    public String getRfid() {
        return rfid;
    }

    public Promise<SoapyUser, Throwable, Void> getUser() {
        final Deferred<SoapyUser, Throwable, Void> deferred = new DeferredObject<>();

        nextTrackIndex = 0;

        if (user == null) {
            dm.when(SoapyWebAPI.getInstance().fetchUserAndTracks(rfid)).done(new DoneCallback<SoapyUser>() {
                public void onDone(SoapyUser user) {
                    Shower.this.user = user;
                    SoapyPlaylist playlist = user.getPlaylist();
                    String lastPlayedSong = playlist.getLastPlayedSong();
                    tracks = user.getTracks();
                    for (int i = 0; i < tracks.size(); i++) {
                        Log.i("Shower", "User has track: " + tracks.get(i));
                        SoapyTrack track = tracks.get(i);
                        if (track.isLocal()) {
                            Log.i("Shower", "Ignoring local track: " + tracks.get(i));
                            tracks.remove(i);
                            i--;
                        }
                        if (track.getURI().equals(lastPlayedSong) && i < tracks.size() - 1) {
                            nextTrackIndex = i + 1;
                        }
                    }
                    for (SoapyTrack track : tracks) {
                        Log.i("Shower", "User has track: " + track);
                    }
                    deferred.resolve(user);
                }
            }).fail(new FailCallback<SoapyWebAPI.SoapyWebError>() {
                public void onFail(SoapyWebAPI.SoapyWebError e) {
                    deferred.reject(e);
                }
            });
        } else {
            deferred.resolve(user);
        }

        return deferred.promise();
    }

    public Promise<String, Throwable, Void> getAccessToken() {
        final Deferred<String, Throwable, Void> deferred = new DeferredObject<>();

        dm.when(getUser()).done(new DoneCallback<SoapyUser>() {
            @Override
            public void onDone(SoapyUser user) {
                deferred.resolve(user.getSpotifyAccessToken());
            }
        }).fail(new FailCallback<Throwable>() {
            @Override
            public void onFail(Throwable result) {
                deferred.reject(result);
            }
        });

        return deferred.promise();
    }

    public SoapyTrack getNextTrack() {
        if (tracks.isEmpty()) {
            return null;
        }

        SoapyTrack ret;


        int originalIndex = nextTrackIndex;

        do {
            nextTrackIndex++;
            nextTrackIndex %= tracks.size();

            // Don't loop forever.
            if (nextTrackIndex == originalIndex) {
                return null;
            }

            ret = tracks.get(nextTrackIndex);
        } while (ret == null || ret.isLocal());

        return ret;
    }
}
