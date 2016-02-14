package net.drewgottlieb.soapy;

import android.util.Log;

import org.jdeferred.Deferred;

import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.Promise;
import org.jdeferred.impl.DefaultDeferredManager;
import org.jdeferred.impl.DeferredObject;

import java.util.Collections;
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

    public SoapyUser getUser() {
        return user;
    }

    public Promise<SoapyUser, Throwable, Void> loadUser() {
        final Deferred<SoapyUser, Throwable, Void> deferred = new DeferredObject<>();

        if (user == null) {
            dm.when(SoapyWebAPI.getInstance().fetchUserAndTracks(rfid)).done(new DoneCallback<SoapyUser>() {
                public void onDone(SoapyUser user) {
                    Shower.this.user = user;
                    SoapyPlaylist playlist = user.getPlaylist();
                    String lastPlayedSong = playlist.getLastPlayedSongUri();
                    tracks = user.getTracks();
                    for (int i = 0; i < tracks.size(); i++) {
                        SoapyTrack track = tracks.get(i);
                        if (track.isLocal()) {
                            Log.i("Shower", "Ignoring local track: " + track);
                            tracks.remove(i);
                            i--;
                            continue;
                        }
                        if (track.getURI().equals(lastPlayedSong)) {
                            nextTrackIndex = i + 1;
                        }
                        Log.i("Shower", "User has track: " + track);
                    }
                    if (tracks.size() > 0) {
                        nextTrackIndex %= tracks.size();
                    }
                    if (user.getPlayback().getPlaybackMode() == SoapyPlayback.PlaybackMode.SHUFFLE) {
                        Collections.shuffle(tracks);
                        nextTrackIndex = 0;
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

    public SoapyTrack getNextTrack() {
        if (tracks.isEmpty()) {
            return null;
        }

        SoapyTrack ret = tracks.get(nextTrackIndex);

        nextTrackIndex++;
        nextTrackIndex %= tracks.size();

        return ret;
    }
}
