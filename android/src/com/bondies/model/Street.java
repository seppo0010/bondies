package com.bondies.model;

import java.util.ArrayList;

import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

public class Street {
	private int id;
	private String name;
	private String fullName;

	public Street(int id, String name, String fullName) {
		this.id = id;
		this.name = name;
		this.fullName = fullName;
	}

	public int getId() {
		return id;
	}
	public void setId(int id) {
		this.id = id;
	}
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public String getFullName() {
		return fullName;
	}
	public void setFullName(String fullName) {
		this.fullName = fullName;
	}

	public String toString() {
		return fullName;
	}

	static public ArrayList<Street> find(String name, int limit) {
		SQLiteDatabase database = Database.getInstance();
		Cursor result = database.rawQuery("SELECT * FROM street WHERE name LIKE '%" + name.replace("'", "\\'").replace("%", "\\%") + "%'" + (limit > 0 ? " LIMIT " + String.valueOf(limit) : ""), new String[]{});

		ArrayList<Street> response = new ArrayList<Street>();
		int c = result.getCount();
		for (int i = 0; i < c; i++) {
			result.moveToPosition(i);
			response.add(new Street(result.getInt(0), result.getString(1), result.getString(2)));
		}
		result.close();
		return response;
	}
}
