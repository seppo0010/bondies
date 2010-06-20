package com.bondies.activities;


import java.util.ArrayList;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.model.Node;
import com.bondies.model.NodeNotFoundException;
import com.bondies.model.Route;
import com.bondies.model.Street;

public class Search extends Activity implements OnClickListener {
	final static private int FROM_STREET= 1;
	final static private int FROM_STREET_INTERSECTION = 2;
	final static private int TO_STREET = 3;
	final static private int TO_STREET_INTERSECTION = 4;
	private Button changeStreetFrom;
	private Street fromStreet;
	private Button changeStreetIntersectionFrom;
	private Street fromStreetIntersection;
	private Node fromNode;
	private View fromOk = null;

	private Street toStreet;
	private Button changeStreetTo;
	private Street toStreetIntersection;
	private Button changeStreetIntersectionTo;
	private Node toNode;
	private View toOk = null;

	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		getWindow().requestFeature(Window.FEATURE_INDETERMINATE_PROGRESS);
		setContentView(R.layout.search);
		changeStreetFrom = (Button)findViewById(R.id.fromStreet);
		changeStreetFrom.setOnClickListener(this);
		changeStreetIntersectionFrom = (Button)findViewById(R.id.fromStreetIntersection);
		changeStreetIntersectionFrom.setOnClickListener(this);
		fromOk = findViewById(R.id.fromOk);
		fromOk.setVisibility(View.INVISIBLE);

		changeStreetTo = (Button)findViewById(R.id.toStreet);
		changeStreetTo.setOnClickListener(this);
		changeStreetIntersectionTo = (Button)findViewById(R.id.toStreetIntersection);
		changeStreetIntersectionTo.setOnClickListener(this);
		toOk = findViewById(R.id.toOk);
		toOk.setVisibility(View.INVISIBLE);

		Button search = (Button)findViewById(R.id.search);
		search.setOnClickListener(new OnClickListener() {
			final private Runnable fromOk = new Runnable() {
				public void run() {
					Search.this.fromOk.setVisibility(View.VISIBLE);
				}
			};
			
			final private Runnable toOk = new Runnable() {
				public void run() {
					Search.this.toOk.setVisibility(View.VISIBLE);
				}
			};
			
			public void onClick(View v) {
				final Handler handler = new Handler();
				Search.this.fromOk.setVisibility(View.INVISIBLE);
				Search.this.toOk.setVisibility(View.INVISIBLE);
				Search.this.setProgressBarIndeterminateVisibility(true);
				new Thread() {
					public void run() {
						final ArrayList<Route> routes = new ArrayList<Route>();
						if (fromStreet != null && fromStreetIntersection != null && toStreet != null && toStreetIntersection != null) {
							if (validateFromNode()) {
								handler.post(fromOk);
								if (validateToNode()) {
									handler.post(toOk);
									routes.addAll(Route.search(fromNode, toNode, Route.SUBWAY));
								}
							}
						}
						handler.post(new Runnable() {
							public void run() {
								if (fromNode == null) Toast.makeText(Search.this, "From intersection not found", Toast.LENGTH_LONG).show();
								else if (toNode == null) Toast.makeText(Search.this, "To intersection not found", Toast.LENGTH_LONG).show();
								else {
									Log.d("routes", routes.size() + " routes found");
									Toast.makeText(Search.this, routes.size() + " routes found", Toast.LENGTH_LONG).show();
								}
								Search.this.setProgressBarIndeterminateVisibility(false);
							}
						});
					}
				}.start();
			}
		});
	}

	public void onClick(View v) {
		int requestCode = 0;
		if (v == changeStreetFrom) requestCode = FROM_STREET;
		else if (v == changeStreetIntersectionFrom) requestCode = FROM_STREET_INTERSECTION;
		else if (v == changeStreetTo) requestCode = TO_STREET;
		else if (v == changeStreetIntersectionTo) requestCode = TO_STREET_INTERSECTION;
		if (requestCode == 0) return;
		Intent intent = new Intent(Search.this, SelectStreet.class);
		startActivityForResult(intent, requestCode);
	}

	protected void onActivityResult (int requestCode, int resultCode, Intent data) {
		if (resultCode != Activity.RESULT_OK) return;
		int streetId = data.getIntExtra("street_id", -1);
		if (streetId > -1) {
			Street street = Street.getById(streetId);
			if (requestCode == FROM_STREET) {
				fromStreet = street;
				changeStreetFrom.setText(street.getName());
				fromNode = null;
			}
			else if (requestCode == FROM_STREET_INTERSECTION) {
				fromStreetIntersection = street;
				changeStreetIntersectionFrom.setText(street.getName());
				fromNode = null;
			}
			else if (requestCode == TO_STREET) {
				toStreet = street;
				changeStreetTo.setText(street.getName());
				toNode = null;
			}
			else if (requestCode == TO_STREET_INTERSECTION) {
				toStreetIntersection = street;
				changeStreetIntersectionTo.setText(street.getName());
				toNode = null;
			}
		}
	}

	private boolean validateFromNode() {
		try {
			fromNode = Node.getByStreets(fromStreet.getId(), fromStreetIntersection.getId());
			return true;
		} catch (NodeNotFoundException e) {
			fromNode = null;
		}
		return false;
	}

	private boolean validateToNode() {
		try {
			toNode = Node.getByStreets(toStreet.getId(), toStreetIntersection.getId());
			return true;
		} catch (NodeNotFoundException e) {
			toNode = null;
		}
		return false;
	}
}