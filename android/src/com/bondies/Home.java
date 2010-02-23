package com.bondies;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

public class Home extends Activity {
	private Button updater;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        updater = (Button)findViewById(R.id.downloader);
        updater.setOnClickListener(new View.OnClickListener() {
			public void onClick(View v) {
				openUpdater();
			}
		});
    }

    private void openUpdater() {
        Intent intent = new Intent();
        intent.setClass(getApplicationContext(), com.bondies.activities.Updater.class);
        this.startActivity(intent);
    }
}