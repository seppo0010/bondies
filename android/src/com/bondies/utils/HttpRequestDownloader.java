package com.bondies.utils;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.lang.reflect.Method;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;

import android.net.Uri;
import android.os.Handler;

public class HttpRequestDownloader extends HttpRequest {
	File file = null;
	File downloadFolder = null;
	private static int BUFFER_SIZE = 1024;
	long length = 0;
	private boolean allowResume = false;

	protected Object updateCallback;
	protected Method updateMethod;

	protected Handler updateRequestHandler = new Handler();
	protected Runnable updateRequestRunnable = new Runnable() {
		public void run() {
			callUpdate();
		}
	};

	public long getTotalLength() { return length; }
	public long getReadLength() { return file.length(); }

	public void setAllowResume(boolean _allowResume) {
		allowResume = _allowResume;
		// TODO: implement download resume
		allowResume = false;
	}
	public void setOnUpdateCall(Object _callback, Method _method) {
		updateCallback = _callback;
		updateMethod = _method;
	}

	private void callUpdate() {
		try {
			updateMethod.invoke(updateCallback, new Object[] { this });
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public void setDownloadFolder(String _downloadFolder) {
		downloadFolder = new File(_downloadFolder);
		downloadFolder.mkdirs();
	}
	public void requestUrl(String _url) {
		Uri uri = Uri.parse(_url);
		String filename = uri.getLastPathSegment();
		if (downloadFolder == null) downloadFolder = new File("/sdcard");
		file = new File(downloadFolder.getAbsolutePath() + "/" + filename);
		if (file.exists() && allowResume) {
			this.addHeader("Range", "bytes=" + String.valueOf(file.length()) + "-");
		}
		super.requestUrl(_url);
	}

	public File getFile() {
		return file;
	}

	protected void startRequest() {
		(new HttpRequestDownloaderThread()).start();
	}

	private class HttpRequestDownloaderThread extends HttpRequestThread {
		public void run() {
			HttpClient client = getClient();
			HttpPost post = getPost();

			try {
				HttpResponse _response = client.execute(post);
				HttpEntity resEntity = _response.getEntity();
				if (resEntity == null) {
					finished();
					return;
				}
				length = resEntity.getContentLength();
				httpStatus = _response.getStatusLine().getStatusCode();
//				if (httpStatus != 206) file.delete();
				FileOutputStream output = new FileOutputStream(file);
				InputStream input = resEntity.getContent();
				int offset = 0;
				byte[] b = new byte[BUFFER_SIZE];
				while (true) {
					int read_size = input.read(b, 0, BUFFER_SIZE);
					if (read_size == -1) break;
					output.write(b, 0, read_size);
					offset += BUFFER_SIZE;
					updateRequestHandler.post(updateRequestRunnable);
				}
			} catch (Exception e) {
				e.printStackTrace();
			}
			finished();
		}
	}
}
