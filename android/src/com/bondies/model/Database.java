package com.bondies.model;

import android.database.sqlite.SQLiteDatabase;


public class Database {
	static private SQLiteDatabase database = null;

	static public SQLiteDatabase getInstance() {
		if (database == null)
			database = SQLiteDatabase.openDatabase("/sdcard/bondies/sqlite.db", null, SQLiteDatabase.OPEN_READONLY);
		return database;
	}
}
