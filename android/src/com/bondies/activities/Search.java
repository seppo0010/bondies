package com.bondies.activities;


import android.app.Activity;
import android.os.Bundle;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.model.NodeNotFoundException;
import com.bondies.model.Route;

public class Search extends Activity {
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.search);
		//["from_id"]=> string(4) "1280"  ["from_intersection_id"]=> string(4) "1587" ["to_id"]=> string(4) "1280" ["to_intersection_id"]=> string(4) "1583"
		try {
			Route.search(1280,1587,1280,1583,Route.SUBWAY);
		} catch (NodeNotFoundException e) {
			Toast.makeText(this, this.getString(R.string.node_not_found), Toast.LENGTH_SHORT);
		}
	}
}
