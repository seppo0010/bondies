package com.bondies.activities;


import java.util.ArrayList;
import java.util.Timer;
import java.util.TimerTask;

import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.view.KeyEvent;
import android.view.View;
import android.view.View.OnKeyListener;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.model.NodeNotFoundException;
import com.bondies.model.Route;
import com.bondies.model.Street;

public class Search extends Activity {
	static private int AUTOCOMPLETE_DISPLAY = 10;

	private AutoCompleteTextView fromStreet;
	private ArrayAdapter<String> fromStreetAdapter;
	private AutoCompleteTextView fromStreetIntersection;

	private String lastSearch = null;
	private Handler handler = new Handler();
	private Runnable runnable = new Runnable() {
		public void run() {
			fromStreetAdapter.notifyDataSetChanged();
			fromStreet.showDropDown();
		}
	};
	private Timer refresh;

	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.search);
		fromStreet = (AutoCompleteTextView) findViewById(R.id.from_street);
		fromStreet.setOnKeyListener(new OnKeyListener() {
			public boolean onKey(View v, int keyCode, KeyEvent event) {
				if (event.getAction() == KeyEvent.ACTION_UP) {
					// We already have a small list of candidates, and it is still valid? awesome!
					if (lastSearch != null && fromStreetAdapter.getCount() < AUTOCOMPLETE_DISPLAY && fromStreet.getText().toString().startsWith(lastSearch)) return false;
					if (refresh != null) refresh.cancel();
					refresh = new Timer();
					refresh.schedule(new TimerTask() {
						@Override
						public void run() {
							refreshAutocomplete(fromStreet);
						}
					}, 2000);
				}
				return false;
			}
		});
		fromStreetAdapter = new ArrayAdapter<String>(this, android.R.layout.simple_dropdown_item_1line, new String[]{});
		fromStreet.setAdapter(fromStreetAdapter);
		fromStreetIntersection = (AutoCompleteTextView) findViewById(R.id.from_street_intersection);
		//["from_id"]=> string(4) "1280"  ["from_intersection_id"]=> string(4) "1587" ["to_id"]=> string(4) "1280" ["to_intersection_id"]=> string(4) "1583"
	}

	private void refreshAutocomplete(AutoCompleteTextView field) {
		refresh.cancel();
		refresh = null;
		if (field == fromStreet) {
			String search = fromStreet.getText().toString();
			if (search.length() == 0) return;
			fromStreetAdapter.setNotifyOnChange(false);
			fromStreetAdapter.clear();
			ArrayList<Street> streets = Street.find(search, AUTOCOMPLETE_DISPLAY);
			int c = streets.size();
			for (int i = 0; i < c; i++) {
				fromStreetAdapter.add(streets.get(i).getFullName());
			}
			lastSearch = search;
			handler.post(runnable);
		}
	}

	public void search() {
		final Activity activity = this;
		(new Thread() {
			public void run() {
				try {
					Route.search(1280,1587,1280,1583,Route.SUBWAY);
				} catch (NodeNotFoundException e) {
					Toast.makeText(activity, activity.getString(R.string.node_not_found), Toast.LENGTH_SHORT);
				}
			}
		}).start();
	}
}
