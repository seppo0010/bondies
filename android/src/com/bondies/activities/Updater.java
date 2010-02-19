package com.bondies.activities;

import android.app.Activity;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.utils.HttpRequest;

public class Updater extends Activity {
	EditText url;
	Button checkVersion;
	SharedPreferences settings = this.getApplicationContext().getSharedPreferences("com.bondies", 0);

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.updater);
        url = (EditText) findViewById(R.id.url);
    	synchronized(settings) {
    		String userUrl = settings.getString("defaultURL", "");
        	if (userUrl.length() > 0) url.setText(userUrl);
    	}
        url.setOnFocusChangeListener(new View.OnFocusChangeListener() {
			public void onFocusChange(View v, boolean hasFocus) {
				if (!hasFocus) setBaseUrl();
			}
		});
        checkVersion = (Button) findViewById(R.id.checkVersion);
        checkVersion.setOnClickListener(new View.OnClickListener() {
			public void onClick(View v) {
				checkVersion();
			}
		});
    }

    private void setBaseUrl() {
    	synchronized(settings) {
    		SharedPreferences.Editor editor = settings.edit();
    		editor.putString("defaultURL", url.getText().toString());
    		editor.commit();
    	}
    }

    private void checkVersion() {
    	Toast.makeText(getApplicationContext(), "Checking version...", Toast.LENGTH_SHORT).show();

    	HttpRequest request = new HttpRequest();
    	try {
			request.onFinishCall(this, this.getClass().getMethod("checkVersionResponse", HttpRequest.class), this.getClass().getMethod("requestFailed", HttpRequest.class));
			request.requestUrl(url.getText().toString());
		} catch (Exception e) {
		}
    }

    public void checkVersionResponse(HttpRequest response) {
    	String response_str = response.getResponse();
    	Integer response_int = Integer.parseInt(response_str);
    	int latestVersion = settings.getInt("latestVersion", 0);
    	if (response_int > latestVersion) {
        	Toast.makeText(getApplicationContext(), "There is a new version available", Toast.LENGTH_SHORT).show();
    		synchronized(settings) {
    			SharedPreferences.Editor editor = settings.edit();
    			editor.putInt("latestVersion", response_int);
    			editor.commit();
    		}
    	} else {
        	Toast.makeText(getApplicationContext(), "Your version is up to date", Toast.LENGTH_SHORT).show();
    	}
    }

    public void requestFailed(HttpRequest response) {
    	Toast.makeText(getApplicationContext(), "Failed with code " + String.valueOf(response.getHttpStatus()), Toast.LENGTH_SHORT).show();
    }
}
