package net.drewgottlieb.soapy;

import android.app.Activity;
import android.content.res.TypedArray;
import android.os.Bundle;
import android.app.Fragment;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;

public class StatusStrip extends Fragment {
    private static final String ARG_CANCEL_ENABLED = "cancelEnabled";

    private Boolean cancelEnabled = null;

    private OnFragmentInteractionListener mListener;

    public static StatusStrip newInstance(boolean cancelEnabled) {
        StatusStrip fragment = new StatusStrip();
        Bundle args = new Bundle();
        args.putBoolean(ARG_CANCEL_ENABLED, cancelEnabled);
        fragment.setArguments(args);
        return fragment;
    }

    public StatusStrip() {
        // Required empty public constructor
    }

    @Override
    public void onInflate(Activity activity, AttributeSet attrs, Bundle savedInstanceState) {
        super.onInflate(activity, attrs, savedInstanceState);

        TypedArray a = activity.obtainStyledAttributes(attrs, R.styleable.StatusStrip);

        boolean defaultValue = (cancelEnabled == null ? false : cancelEnabled);
        cancelEnabled = new Boolean(a.getBoolean(
                R.styleable.StatusStrip_showCancelButton, defaultValue));

        a.recycle();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {
            cancelEnabled = getArguments().getBoolean(ARG_CANCEL_ENABLED);
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_status_strip, container, false);

        Button cancelButton = (Button) view.findViewById(R.id.cancel_button);
        cancelButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mListener.onCancelPressed();
            }
        });

        return view;
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
        try {
            mListener = (OnFragmentInteractionListener) activity;
        } catch (ClassCastException e) {
            throw new ClassCastException(activity.toString()
                    + " must implement OnFragmentInteractionListener");
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mListener = null;
    }

    public interface OnFragmentInteractionListener {
        public void onCancelPressed();
    }
}
