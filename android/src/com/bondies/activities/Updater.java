package com.bondies.activities;

import android.app.Activity;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.utils.HttpRequest;
import com.bondies.utils.HttpRequestDownloader;
import com.bondies.utils.HttpRequestSimple;

public class Updater extends Activity {
	private EditText url;
	private Button checkVersion;
	private SharedPreferences settings;
	private Button download;
	private HttpRequestDownloader downloader = null;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.updater);
        url = (EditText) findViewById(R.id.url);
        settings = this.getSharedPreferences("com.bondies", 0);
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
        download = (Button) findViewById(R.id.download);
        download.setOnClickListener(new View.OnClickListener() {
			public void onClick(View arg0) {
				startDownloading();
			}
		});
    }

    protected void startDownloading() {
    	if (downloader != null) return;
    	setBaseUrl();
    	downloader = new HttpRequestDownloader();
    	try {
    		downloader.setOnFinishCall(this, this.getClass().getMethod("downloadFinished", HttpRequestDownloader.class), this.getClass().getMethod("downloadFailed", HttpRequestDownloader.class));
    		downloader.setOnUpdateCall(this, this.getClass().getMethod("downloadUpdated", HttpRequestDownloader.class));
    		downloader.requestUrl(url.getText().toString() + "sqlite.db");
		} catch (Exception e) {
			e.printStackTrace();
			downloader = null;
		}
	}
 
    public void downloadUpdated(HttpRequestDownloader _downloader) {
    	long a = _downloader.getReadLength();
    	float percent = _downloader.getReadLength() / _downloader.getTotalLength();
    	Log.d("percent", String.valueOf(percent) 
    		+ String.valueOf(a));
    	Toast.makeText(getApplicationContext(), "Downloaded: " + String.valueOf(_downloader.getReadLength() / _downloader.getTotalLength() * 100) + "%", Toast.LENGTH_SHORT).show();
    }

    public void downloadFinished(HttpRequestDownloader _downloader) {
    	Toast.makeText(getApplicationContext(), "Finished", Toast.LENGTH_SHORT).show();
    	downloader = null;
    }

    public void downloadFailed(HttpRequestDownloader _downloader) {
    	Toast.makeText(getApplicationContext(), "Failed", Toast.LENGTH_SHORT).show();
    	downloader = null;
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
    	setBaseUrl();

    	HttpRequest request = new HttpRequestSimple();
    	try {
			request.setOnFinishCall(this, this.getClass().getMethod("checkVersionResponse", HttpRequestSimple.class), this.getClass().getMethod("requestFailed", HttpRequestSimple.class));
			request.requestUrl(url.getText().toString() + "sqlite_version.php");
		} catch (Exception e) {
		}
    }

    public void checkVersionResponse(HttpRequestSimple response) {
    	String response_str = response.getResponse();
    	Integer response_int = 0;
    	try {
    		response_int = Integer.parseInt(response_str);
    	} catch (Exception e) {}
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

    public void requestFailed(HttpRequestSimple response) {
    	Toast.makeText(getApplicationContext(), "Failed with code " + String.valueOf(response.getHttpStatus()), Toast.LENGTH_SHORT).show();
    }
}
