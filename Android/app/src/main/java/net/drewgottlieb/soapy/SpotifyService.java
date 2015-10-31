package net.drewgottlieb.soapy;

import android.app.Service;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Binder;
import android.os.IBinder;
import android.util.Log;

import com.spotify.sdk.android.player.ConnectionStateCallback;
import com.spotify.sdk.android.player.Player;
import com.spotify.sdk.android.player.PlayerNotificationCallback;
import com.spotify.sdk.android.player.PlayerState;
import com.spotify.sdk.android.player.Spotify;
import com.spotify.sdk.android.player.Config;

import org.jdeferred.Deferred;
import org.jdeferred.DeferredManager;
import org.jdeferred.DoneCallback;
import org.jdeferred.FailCallback;
import org.jdeferred.Promise;
import org.jdeferred.impl.DefaultDeferredManager;
import org.jdeferred.impl.DeferredObject;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class SpotifyService extends Service implements PlayerNotificationCallback, ConnectionStateCallback {
    public static final int NUM_SHOWERS = 2;
    private static String TAG = "SpotifyService";

    private SoapyPreferences preferences = null;
    private final SpotifyBinder mBinder = new SpotifyBinder();
    private Player mPlayer = null;
    private ExecutorService executorService = Executors.newCachedThreadPool();
    private DeferredManager dm = new DefaultDeferredManager(executorService);
    private Shower[] showers = new Shower[NUM_SHOWERS];
    private int currentlyPlayingShower = -1; // index of shower currently playing music, or -1 if none.
    private String currentAccessToken = null;
    private String nextAccessToken = null;
    private int trackStartSkips = 0;

    public class SpotifyBinder extends Binder {
        SpotifyService getService() {
            return SpotifyService.this;
        }
    }

    private BroadcastReceiver receiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            int index = intent.getIntExtra("index", -1);

            switch (intent.getAction()) {
                case ArduinoService.DOOR_INTENT:
                    if (index < 0) {
                        return;
                    }

                    if (intent.getBooleanExtra("closed", false)) {
                        SpotifyService.this.doorClosed(index);
                    } else {
                        SpotifyService.this.doorOpened(index);
                    }

                    break;

                case ArduinoService.RFID_INTENT:
                    if (index < 1) {
                        return;
                    }

                    String rfid = intent.getStringExtra("rfid");
                    SpotifyService.this.showerRfidTapped(index - 1, rfid);

                    break;

                case ArduinoService.DISCONNECTED_INTENT:
                    SpotifyService.this.arduinoDisconnected();
                    break;
            }
        }
    };

    public SpotifyService() {
        preferences = SoapyPreferences.getInstance();
    }

    public void onPlaybackError(ErrorType errorType, String errorDetails) {
        Log.w(TAG, "Spotify playback error (" + errorType + "): " + errorDetails);
        resetShower(currentlyPlayingShower);
    }

    public void onPlaybackEvent(EventType eventType, PlayerState playerState) {
        Log.i(TAG, "Spotify playback event! Value: " + eventType.toString());

        switch (eventType) {
            case LOST_PERMISSION:
                Log.i(TAG, "Lost Spotify permission. Resetting shower " + currentlyPlayingShower);
                resetShower(currentlyPlayingShower);
                break;
            case TRACK_CHANGED:
                if (trackStartSkips > 0) {
                    trackStartSkips--;
                    break;
                }

                Log.i(TAG, "Song ended. Playing next song...");
                playNextSong();
                break;
        }
    }

    public void onConnectionMessage(String message) {
        Log.i(TAG, "Spotify connection message: " + message);
    }

    public void onLoggedIn() {
        Log.i(TAG, "Spotify logged in!");
        playCurrentNextTrack();
    }

    public void playCurrentNextTrack() {
        if (currentlyPlayingShower < 0 || currentlyPlayingShower >= showers.length) {
            return;
        }

        SoapyTrack track = showers[currentlyPlayingShower].getNextTrack();
        if (track == null) {
            Log.w(TAG, "Couldn't get a next track. Resetting shower.");
            resetShower(currentlyPlayingShower);
        } else {
            playTrack(track);
        }
    }

    public void onLoggedOut() {
        Log.i(TAG, "Spotify logged out!");
        if (nextAccessToken != null) {
            if (mPlayer.login(nextAccessToken)) {
                currentAccessToken = nextAccessToken;
                nextAccessToken = null;
            } else {
                nextAccessToken = null;
                currentAccessToken = null;
                resetShower(currentlyPlayingShower);
            }
        }
    }

    public void onLoginFailed(Throwable error) {
        Log.e(TAG, "Spotify login failed: " + error.getMessage());
        resetShower(currentlyPlayingShower);
    }

    public void onTemporaryError() {
        Log.w(TAG, "Spotify had a temporary error!");
        resetShower(currentlyPlayingShower);
    }

    protected Promise<Boolean, Void, Void> stopPlayer() {
        final Deferred<Boolean, Void, Void> deferred = new DeferredObject<>();

        mPlayer.logout();
        currentAccessToken = null;
        deferred.resolve(true);

        return deferred.promise();
    }

    protected Promise<Void, Throwable, Void> startPlayer(final String accessToken) {
        final Deferred<Void, Throwable, Void> deferred = new DeferredObject<>();

        if (mPlayer == null) {
            Log.i(TAG, "Creating player for token " + accessToken.substring(0, 50) + "...");

            currentAccessToken = accessToken;
            Config playerConfig = new Config(SpotifyService.this, accessToken, preferences.getSpotifyClientId());
            mPlayer = Spotify.getPlayer(playerConfig, this, new Player.InitializationObserver() {
                @Override
                public void onInitialized(Player player) {
                    mPlayer.addConnectionStateCallback(SpotifyService.this);
                    mPlayer.addPlayerNotificationCallback(SpotifyService.this);

                    if (mPlayer.isLoggedIn()) {
                        // We're still logged in and thus won't get the onLoggedIn callback.
                        // Let's call it ourselves, eh?
                        onLoggedIn();
                    }

                    deferred.resolve(null);
                }

                @Override
                public void onError(Throwable throwable) {
                    Log.e(TAG, "Could not initialize player: " + throwable.getMessage());
                    deferred.reject(new Exception(throwable));
                }
            });
        } else {
            Log.i(TAG, "Reauthing player for token " + accessToken.substring(0, 50) + "...");

            if (accessToken.equals(currentAccessToken)) {
                onLoggedIn();
            } else {
                Log.i(TAG, "Logging out old player...");
                nextAccessToken = accessToken;
                if (!mPlayer.logout()) {
                    Log.i(TAG, "Old player was already logging out. Logging in...");
                    nextAccessToken = null;
                    mPlayer.login(accessToken);
                }
            }
        }

        return deferred.promise();
    }

    public Shower getCurrentShower() {
        if (currentlyPlayingShower >= 0 && currentlyPlayingShower < showers.length) {
            return showers[currentlyPlayingShower];
        }

        return null;
    }

    protected void playTrack(final SoapyTrack track) {
        trackStartSkips = 1;
        mPlayer.play(track.getURI());

        Shower shower = getCurrentShower();
        if (shower != null) {
            SoapyWebAPI.getInstance().setLastSongPlayed(shower.getRfid(), track.getURI());
            dm.when(shower.getUser()).done(new DoneCallback<SoapyUser>() {
                @Override
                public void onDone(SoapyUser result) {
                    Log.i(TAG, result.getFullName() + " started playing track: " + track);
                }
            }).fail(new FailCallback<Throwable>() {
                @Override
                public void onFail(Throwable result) {
                    Log.i(TAG, "Playing track: " + track);
                }
            });
        } else {
            Log.i(TAG, "Playing track: " + track);
        }
    }

    protected boolean isShowerOccupied(int index) {
        if (index < 0 || index >= showers.length) {
            return false;
        }

        return (showers[index] != null);
    }

    protected boolean isShowerPlayable(int index) {
        if (!isShowerOccupied(index)) {
            return false;
        }

        return showers[index].isPlayable();
    }

    protected boolean isShowerPlayingMusic(int index) {
        if (index < 0) {
            return false;
        }

        return (currentlyPlayingShower == index);
    }

    protected boolean isMusicPlaying() {
        return (currentlyPlayingShower != -1);
    }

    protected void showerRfidTapped(int index, String rfid) {
        Log.i(TAG, "RFID tapped at index " + index);

        // Is the shower even occupied?
        if (!isShowerOccupied(index)) {
            Log.i(TAG, "RFID tapped for shower " + index + " but shower is unoccupied.");

            // TODO: Remember this briefly in case someone taps their RFID before closing the latch.
            return;
        }

        // Was RFID already tapped for this shower's session?
        if (isShowerPlayable(index)) {
            return;
        }

        Shower shower = showers[index];
        shower.setRfid(rfid);

        if (!isMusicPlaying()) {
            playNextSong();
        }
    }

    protected void resetShower(int index) {
        if (index < 0 || index >= NUM_SHOWERS) {
            return;
        }

        boolean wasPlayingMusic = isShowerPlayingMusic(index);
        Log.i(TAG, "Resetting shower " + index + ", and it " +
                   (wasPlayingMusic ? "was" : "wasn't") + " playing music.");
        showers[index] = new Shower();
        if (wasPlayingMusic) {
            playNextSong();
        }
    }

    protected void destroyShower(int index) {
        if (index < 0 || index >= NUM_SHOWERS) {
            return;
        }

        boolean wasPlayingMusic = isShowerPlayingMusic(index);
        Log.i(TAG, "Destroying shower " + index + ", and it " +
                   (wasPlayingMusic ? "was" : "wasn't") + " playing music.");
        showers[index] = null;
        if (wasPlayingMusic) {
            playNextSong();
        }
    }

    protected void arduinoDisconnected() {
        for (int i = 0; i < NUM_SHOWERS; i++) {
            doorOpened(i);
        }
    }

    protected void doorOpened(int index) {
        Log.i(TAG, "Door opened: " + index);
        destroyShower(index);
    }

    protected Promise playShower(final int showerIndex) {
        final Deferred deferred = new DeferredObject();

        currentlyPlayingShower = showerIndex;

        dm.when(showers[showerIndex].getAccessToken()).done(new DoneCallback<String>() {
            @Override
            public void onDone(String accessToken) {
                Log.i(TAG, "Got access token for shower " + showerIndex + ": " +
                        accessToken.substring(0, 50) + "...");

                dm.when(startPlayer(accessToken)).done(new DoneCallback<Void>() {
                    @Override
                    public void onDone(Void result) {
                        //currentlyPlayingShower = showerIndex;
                    }
                }).fail(new FailCallback<Throwable>() {
                    @Override
                    public void onFail(Throwable result) {
                        Log.e(TAG, "Failed to start player when playing shower " +
                                showerIndex + ". Error: " + result.getMessage());
                        deferred.reject(null);
                    }
                });
            }
        })
        .fail(new FailCallback<Throwable>() {
            @Override
            public void onFail(Throwable result) {
                Log.e(TAG, "Failed to fetch accessToken to start music for shower " +
                        showerIndex + ". Error: " + result.getMessage());
                deferred.reject(null);
            }
        });

        Promise promise = deferred.promise();

        promise.fail(new FailCallback() {
            @Override
            public void onFail(Object result) {
                Log.i(TAG, "Setting currentlyPlayingShower to -1 because startShower() failed.");
                currentlyPlayingShower = -1;
            }
        });

        return promise;
    }

    protected void playNextSong() {
        Log.i(TAG, "Playing next song!");

        int nextShowerIndex = -1;
        for (int i = 0; i < NUM_SHOWERS; i++) {

            // Loop through all available showers, starting with the current shower.
            // If no shower is playing music, currentlyPlayingShower will be -1 so we'll
            // still try the first shower.
            final int showerIndex = (currentlyPlayingShower + i + 1) % NUM_SHOWERS;

            Log.i(TAG, "Checking shower " + showerIndex + "; Playable? " + isShowerPlayable(showerIndex));

            if (isShowerPlayable(showerIndex)) {
                nextShowerIndex = showerIndex;
                break;
            }
        }

        // We're not going to play any shower. Just stop the music.
        if (nextShowerIndex == -1) {
            Log.i(TAG, "Found no shower to play from. Just stopping music.");
            stopPlayer();
            currentlyPlayingShower = -1;
            return;
        }

        final int finalShowerIndex = nextShowerIndex;

        dm.when(playShower(nextShowerIndex))
        .fail(new FailCallback() {
            @Override
            public void onFail(Object result) {
                Log.e(TAG, "Failed to start player for shower " +
                        finalShowerIndex + ". Removing shower.");
                resetShower(finalShowerIndex);
                playNextSong();
            }
        });

    }

    protected void doorClosed(int index) {
        Log.i(TAG, "Door closed: " + index);

        if (showers[index] != null) {
            destroyShower(index);
        }

        showers[index] = new Shower();
    }

    @Override
    public IBinder onBind(Intent intent) {
        return mBinder;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        return START_STICKY;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        IntentFilter filter = new IntentFilter();
        filter.addAction(ArduinoService.DOOR_INTENT);
        filter.addAction(ArduinoService.RFID_INTENT);
        filter.addAction(ArduinoService.DISCONNECTED_INTENT);
        registerReceiver(receiver, filter);
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        unregisterReceiver(receiver);

        if (mPlayer != null) {
            Log.i(TAG, "Destroying Spotify service.");
            Spotify.destroyPlayer(mPlayer);
            mPlayer = null;
        }
    }
}
