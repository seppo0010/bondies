package com.bondies.activities;

import java.util.ArrayList;

import android.app.Activity;
import android.app.ListActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.View;
import android.view.View.OnKeyListener;
import android.widget.ArrayAdapter;
import android.widget.EditText;

import com.bondies.R;
import com.bondies.model.Street;

public class SelectStreet extends ListActivity {
	final static private int STREET_LIMIT = 10;
	private EditText criteria;
	private String lastSearch = null;
	private ArrayList<Street> options = new ArrayList<Street>();
	private ArrayAdapter<String> streetAdapter = null;

	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.select_street);
		criteria = (EditText)findViewById(R.id.criteria);
		criteria.setOnKeyListener(new OnKeyListener() {
			public boolean onKey(View v, int keyCode, KeyEvent event) {
				String search = criteria.getText().toString();
				if (search.equals(lastSearch)) return false;
				ArrayList<Street> streets = Street.find(search, STREET_LIMIT);
				options.clear();
				streetAdapter.clear();
				if (search.equals("")) return false;
				options.addAll(streets);
				streetAdapter.setNotifyOnChange(false);
				for (Street street : streets) {
					streetAdapter.add(street.getFullName());
				}
				streetAdapter.notifyDataSetChanged();
				lastSearch = search;
				return false;
			}
		});
		streetAdapter = new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1);
		setListAdapter(streetAdapter);
	}

  	protected void onListItemClick(android.widget.ListView l, android.view.View v, int position, long id) {
		super.onListItemClick(l, v, position, id);
		setStreet(options.get(position));
	}

  	public void setStreet(Street street) {
		Intent intent = new Intent();
		intent.putExtra("street_id", street.getId());
		setResult(Activity.RESULT_OK, intent);
		finish();
	}
}
