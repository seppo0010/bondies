package com.bondies.utils;

import java.io.ByteArrayOutputStream;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;

public class HttpRequestSimple extends HttpRequest {
	protected String response;

	public String getResponse() {
		return response;
	}

	protected void startRequest() {
		(new HttpRequestSimpleThread()).start();
	}

	private class HttpRequestSimpleThread extends HttpRequestThread {
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
