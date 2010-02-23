package com.bondies.utils;

import java.io.File;
import java.lang.reflect.Method;
import java.util.HashMap;
import java.util.Map;

import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.methods.HttpRequestBase;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpParams;

import android.os.Handler;

abstract public class HttpRequest {
	protected String url = "";
	protected HashMap<String, String> headers = new HashMap<String, String>();
	protected HashMap<String, String> postParameters = new HashMap<String, String>();
	protected HashMap<String, String> postFiles = new HashMap<String, String>();
	protected HashMap<String, String> getParameters = new HashMap<String, String>();
	protected Object callback;
	protected Method successMethod;
	protected Method failureMethod;
	protected int httpStatus = 0;
	protected Handler finishedRequestHandler = new Handler();
	protected Runnable finishedRequestRunnable = new Runnable() {
		public void run() {
			if (httpStatus >= 200 && httpStatus < 400) callSuccess();
			else callFailure();
		}
	};

	public void setOnFinishCall(Object _callback, Method success, Method failure) {
		callback = _callback;
		successMethod = success;
		failureMethod = failure;
	}

	public void setOnFinishCall(Object _callback, Method method) {
		callback = _callback;
		successMethod = method;
		failureMethod = method;
	}

	public void requestUrl(String _url) {
		url = _url;
		startRequest();
	}

	protected void startRequest() {
	}

	public void addPostParameter(String key, String value) {
		postParameters.put(key, value);
	}
	public void addPostParameters(HashMap<String,String> headers) {
		postParameters.putAll(headers);
	}

	public void addGetParameter(String key, String value) {
		getParameters.put(key, value);
	}
	public void addGetParameters(HashMap<String,String> headers) {
		getParameters.putAll(headers);
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

	protected void finished() {
		finishedRequestHandler.post(finishedRequestRunnable);
	}

	protected void callSuccess() {
		if (successMethod == null || callback == null) return;
		try {
			successMethod.invoke(callback, new Object[] {this});
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	protected void callFailure() {
		if (failureMethod == null || callback == null) return;
		try {
			failureMethod.invoke(callback, new Object[] {this});
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public int getHttpStatus() {
		return httpStatus;
	}

	protected class HttpRequestThread extends Thread {
		protected HttpClient getClient() {
			DefaultHttpClient client = new DefaultHttpClient();
			return client;
		}

		protected HttpRequestBase getRequest() {
			if (postFiles.size() + postFiles.size() > 0)
				return getPost();
			else
				return getGet();
		}

		protected HttpGet getGet() {
			HttpGet get = new HttpGet(url);

			try {
				HttpParams params = new BasicHttpParams();
				for (Map.Entry<String, String> entry : getParameters.entrySet()) {
					params.setParameter(entry.getKey(), entry.getValue());
				}
				get.setParams(params);
			} catch (Exception e) {
				finished();
				return null;
			}

			for (Map.Entry<String, String> entry : headers.entrySet())
			{
				get.addHeader(entry.getKey(), entry.getValue());
			}
			return get;
		}

		protected HttpPost getPost() {
			HttpPost post = new HttpPost(url);
			MultipartEntity entity = new MultipartEntity();

			for (Map.Entry<String, String> entry : postFiles.entrySet()) {
				entity.addPart(entry.getKey(), new FileBody(new File(entry.getValue())));
			}
			try {
				for (Map.Entry<String, String> entry : postParameters.entrySet()) {
					entity.addPart(entry.getKey(), new StringBody(entry.getValue()));
				}
			} catch (Exception e) {
				finished();
				return null;
			}

			for (Map.Entry<String, String> entry : headers.entrySet())
			{
				post.addHeader(entry.getKey(), entry.getValue());
			}
			post.setEntity(entity);
			return post;
		}
	}
}
