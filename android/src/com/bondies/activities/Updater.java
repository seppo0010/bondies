package com.bondies.activities;

import java.io.File;

import android.app.Activity;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.Toast;

import com.bondies.R;
import com.bondies.utils.GzipDecompressor;
import com.bondies.utils.HttpRequest;
import com.bondies.utils.HttpRequestDownloader;
import com.bondies.utils.HttpRequestSimple;

public class Updater extends Activity {
	private HttpRequestDownloader downloader = null;
	private GzipDecompressor decompressor = null;

	private EditText url;
	private Button checkVersion;
	private SharedPreferences settings;
	private Button download;
	private ProgressBar progressBar;

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

        progressBar = (ProgressBar) findViewById(R.id.progressBar);
        progressBar.setVisibility(View.INVISIBLE);
    }

    public void onPause() {
    	super.onPause();
    	if (downloader != null) {
    		downloader.setOnFinishCall(null, null);
    		downloader.setOnUpdateCall(null, null);
    	}
    	if (decompressor != null) {
    		decompressor.setOnUpdateCall(null, null);
    		decompressor.setOnFinishCall(null, null);
    	}
    }

    public void onResume() {
    	super.onResume();
		this.setDownloaderCalls();
		this.setGzipperCalls();
    }

    private void setDownloaderCalls() {
    	if (downloader == null) return;
    	try {
			downloader.setOnFinishCall(this, this.getClass().getMethod("downloadFinished", HttpRequestDownloader.class), this.getClass().getMethod("downloadFailed", HttpRequestDownloader.class));
			downloader.setOnUpdateCall(this, this.getClass().getMethod("downloadUpdated", HttpRequestDownloader.class));
		} catch (Exception e) {
			e.printStackTrace();
			downloader = null;
		}
    }

    private void setGzipperCalls() {
    	if (decompressor == null) return;
    	try { decompressor.setOnUpdateCall(this, this.getClass().getMethod("gzipUpdated", GzipDecompressor.class)); } catch (Exception e) { e.printStackTrace(); }
    	try { decompressor.setOnFinishCall(this, this.getClass().getMethod("gzipFinished", GzipDecompressor.class)); } catch (Exception e) { e.printStackTrace(); }
    }

    private void setBaseUrl() {
    	synchronized(settings) {
    		SharedPreferences.Editor editor = settings.edit();
    		editor.putString("defaultURL", url.getText().toString());
    		editor.commit();
    	}
    }

    protected void startDownloading() {
    	if (downloader != null) return;
    	Toast.makeText(getApplicationContext(), "Starting Download", Toast.LENGTH_SHORT).show();
    	setBaseUrl();
        progressBar.setVisibility(View.VISIBLE);
		progressBar.setIndeterminate(true);
    	downloader = new HttpRequestDownloader();
    	downloader.setDownloadFolder("/sdcard/bondies/");
    	try {
    		this.setDownloaderCalls();
    		downloader.requestUrl(url.getText().toString() + "sqlite.db.gz");
		} catch (Exception e) {
			e.printStackTrace();
			downloader = null;
		}
	}
 
    public void downloadUpdated(HttpRequestDownloader _downloader) {
		progressBar.setIndeterminate(false);
    	progressBar.setMax((int) _downloader.getTotalLength());
    	progressBar.setProgress((int)_downloader.getReadLength());
    }

    public void downloadFailed(HttpRequestDownloader _downloader) {
    	Toast.makeText(getApplicationContext(), "Failed", Toast.LENGTH_SHORT).show();
    	downloader = null;
    }

    public void downloadFinished(HttpRequestDownloader _downloader) {
    	Toast.makeText(getApplicationContext(), "Finished Downloading", Toast.LENGTH_SHORT).show();
    	progressBar.setIndeterminate(true);
    	File downloadedFile = downloader.getFile();
    	File uncompressedFile = new File("/sdcard/bondies/sqlite.db");
    	decompressor = new GzipDecompressor();
    	decompressor.setSource(downloadedFile);
    	decompressor.setTarget(uncompressedFile);
    	this.setGzipperCalls();
    	decompressor.startDecompressing();
    	downloader = null;
    }

    public void gzipUpdated(GzipDecompressor decompressor) {
    	progressBar.setIndeterminate(false);
    	progressBar.setMax(decompressor.getTotalSize());
    	progressBar.setProgress(decompressor.getReadedSize());
    }

    public void gzipFinished(GzipDecompressor decompressor) {
        progressBar.setVisibility(View.INVISIBLE);
    	Toast.makeText(getApplicationContext(), "Finished Decompressing", Toast.LENGTH_SHORT).show();
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
