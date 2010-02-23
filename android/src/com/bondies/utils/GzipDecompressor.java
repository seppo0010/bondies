package com.bondies.utils;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.lang.reflect.Method;
import java.util.zip.GZIPInputStream;

import android.os.Handler;

public class GzipDecompressor {
	private boolean succeeded = false;
	private File source;
	private File target;
	private Object updateObject;
	private Method updateMethod;
	private Handler updateHandler = new Handler();
	private Runnable updateRunnable = new Runnable() {
		public void run() {
			callUpdate();
		}
	};
	private Object finishObject;
	private Method finishMethod;
	private Handler finishHandler = new Handler();
	private Runnable finishRunnable = new Runnable() {
		public void run() {
			callFinish();
		}
	};
	private int totalReaded;
	private int totalSize;
	static private int BUFFER_SIZE = 1024;

	public void setSource(File _source) { source = _source; }
	public void setTarget(File _target) { target = _target; }

	public boolean hasSucceeded() { return succeeded; }

	public int getReadedSize() { return totalReaded; } 
	public int getTotalSize() { return totalSize; } 

	public void setOnUpdateCall(Object _updateObject, Method _updateMethod) {
		updateObject = _updateObject;
		updateMethod = _updateMethod;
	}
	
	private void callUpdate() {
		if (updateMethod == null || updateObject == null) return;
		try {
			updateMethod.invoke(updateObject, new Object[] { this });
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	private void callFinish() {
		if (finishMethod == null || finishObject == null) return;
		try {
			finishMethod.invoke(finishObject, new Object[] { this });
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public void setOnFinishCall(Object _finishObject, Method _finishMethod) {
		finishObject = _finishObject;
		finishMethod = _finishMethod;
	}

	public void startDecompressing() {
		(new GzipDecompressorThread()).start();
	}

	private class GzipDecompressorThread extends Thread {
		public void run() {
			succeeded = false;
			try {
				totalReaded = 0;
				totalSize = (int)source.length();
				GZIPInputStream stream = new GZIPInputStream(new FileInputStream(source));
				FileOutputStream outputStream = new FileOutputStream(target);
				byte[] buffer = new byte[BUFFER_SIZE];
				while (true) {
					int readed = stream.read(buffer, 0, BUFFER_SIZE);
					if (readed == -1) break;
					totalReaded += readed;
					outputStream.write(buffer, 0, readed);
					updateHandler.post(updateRunnable);
				}
				succeeded = true;
			} catch (Exception e) {
				e.printStackTrace();
			}
			finishHandler.post(finishRunnable);
		}
	}
}
