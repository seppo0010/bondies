package com.bondies.utils;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.lang.reflect.Method;
import java.util.HashMap;
import java.util.Map;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.DefaultHttpClient;

import android.os.Handler;

public class HttpRequest {
	private String url = "";
	private HashMap<String, String> headers = new HashMap<String, String>();
	private HashMap<String, String> postParameters = new HashMap<String, String>();
	private HashMap<String, String> postFiles = new HashMap<String, String>();
	private Object callback;
	private Method successMethod;
	private Method failureMethod;
	int httpStatus = 0;
	private String response;
	private Handler finishedRequestHandler = new Handler();
	private Runnable finishedRequestRunnable = new Runnable() {
		public void run() {
			if (httpStatus >= 200 && httpStatus < 400) callSuccess();
			else callFailure();
		}
	};

	public String getResponse() {
		return response;
	}

	public void onFinishCall(Object _callback, Method success, Method failure) {
		callback = _callback;
		successMethod = success;
		failureMethod = failure;
	}

	public void onFinishCall(Object _callback, Method method) {
		callback = _callback;
		successMethod = method;
		failureMethod = method;
	}

	public void requestUrl(String _url) {
		url = _url;
		(new HttpRequestThread()).start();
	}

	public void addPostParameter(String key, String value) {
		postParameters.put(key, value);
	}
	public void addPostParameters(HashMap<String,String> headers) {
		postParameters.putAll(headers);
	}

	public void addPostFile(String key, String path) {
		postFiles.put(key, path);
	}
	public void addPostFiles(HashMap<String,String> postFiles) {
		postFiles.putAll(postFiles);
	}

	public void addHeader(String key, String value) {
		headers.put(key, value);
	}
	public void addHeaders(HashMap<String,String> headers) {
		headers.putAll(headers);
	}

	private void finished() {
		finishedRequestHandler.post(finishedRequestRunnable);
	}

	protected void callSuccess() {
		try {
			successMethod.invoke(callback, new Object[] {this});
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	protected void callFailure() {
		try {
			failureMethod.invoke(callback, new Object[] {this});
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public int getHttpStatus() {
		return httpStatus;
	}

	private class HttpRequestThread extends Thread {
		public void run() {
			DefaultHttpClient client = new DefaultHttpClient();
			MultipartEntity entity = new MultipartEntity();
			HttpPost post = new HttpPost(url);

			for (Map.Entry<String, String> entry : postFiles.entrySet()) {
				entity.addPart(entry.getKey(), new FileBody(new File(entry.getValue())));
			}
			try {
				for (Map.Entry<String, String> entry : postParameters.entrySet()) {
					entity.addPart(entry.getKey(), new StringBody(entry.getValue()));
				}
			} catch (Exception e) {
				finished();
				return;
			}
			for (Map.Entry<String, String> entry : headers.entrySet())
			{
				post.addHeader(entry.getKey(), entry.getValue());
			}
			post.setEntity(entity);

			try {
				HttpResponse _response = client.execute(post);
				HttpEntity resEntity = _response.getEntity();
				if (resEntity == null) {
					finished();
					return;
				}
				httpStatus = _response.getStatusLine().getStatusCode();
				ByteArrayOutputStream output = new ByteArrayOutputStream();
				resEntity.writeTo(output);
				byte[] bytes = output.toByteArray();
				response = new String(bytes);
			} catch (Exception e) {
				e.printStackTrace();
			}
			finished();
		}

	}
}
