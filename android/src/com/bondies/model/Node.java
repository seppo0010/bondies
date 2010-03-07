package com.bondies.model;

import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

public class Node {
	private int nodeId;
	private double lat;
	private double lon;
	private String railwayHaltName = null;

	public Node(int nodeId, double lat, double lon, String railwayHaltName) {
		this.nodeId = nodeId;
		this.lat = lat;
		this.lon = lon;
		this.railwayHaltName = railwayHaltName;
	}

	public int getNodeId() {
		return nodeId;
	}
	public void setNodeId(int nodeId) {
		this.nodeId = nodeId;
	}
	public double getLat() {
		return lat;
	}
	public void setLat(double lat) {
		this.lat = lat;
	}
	public double getLon() {
		return lon;
	}
	public void setLon(double lon) {
		this.lon = lon;
	}

	public void setRailwayHaltName(String railwayHaltName) {
		this.railwayHaltName = railwayHaltName;
	}
	public String getRailwayHaltName() {
		return railwayHaltName == null ? "" : railwayHaltName;
	}
	public static Node getByStreets(int streetId, int streetId2) throws NodeNotFoundException {
		SQLiteDatabase database = Database.getInstance();
		Cursor cursor = database.rawQuery("SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = ? AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = ?) LIMIT 1", new String[] { String.valueOf(streetId), String.valueOf(streetId2) });
		if (cursor.getCount() == 0) throw new NodeNotFoundException();
		cursor.moveToFirst();
		Node node = new Node(cursor.getInt(0), cursor.getDouble(1), cursor.getDouble(2), null);
		return node;
	}
	public static Node getById(int nodeId) throws NodeNotFoundException {
		SQLiteDatabase database = Database.getInstance();
		Cursor cursor = database.rawQuery("SELECT node.id, node.lat, node.lon FROM node WHERE node.id = ? LIMIT 1", new String[] { String.valueOf(nodeId) });
		if (cursor.getCount() == 0) throw new NodeNotFoundException();
		cursor.moveToFirst();
		Node node = new Node(cursor.getInt(0), cursor.getDouble(1), cursor.getDouble(2), null);
		return node;
	}

	public double calculateDistance(double _lat, double _lon) {
		return calculateDistance(lat, lon, _lat, _lon);
	}

	static public double calculateDistance(double _lat1, double _lon1, double _lat2, double _lon2) {
		int unit = 6371;
		double degreeRadius = Math.PI / 180.0;

		double lat_from  = _lat1 * degreeRadius;
		double long_from = _lon1 * degreeRadius;
		double lat_to    = _lat2  * degreeRadius;
		double long_to   = _lon2  * degreeRadius;

		double dist = Math.sin(lat_from) * Math.sin(lat_to) + Math.cos(lat_from) * Math.cos(lat_to) * Math.cos(long_from - long_to);

		return (double) (unit * Math.acos(dist));
	}

	public Rectangle calculateBox(double wide) {
		return Node.calculateBox(lat, lon, wide);
	}
	public static Rectangle calculateBox(double lat, double lon, double wide) {
		Rectangle rectangle = new Rectangle();
        double lat_unit_distance = calculateDistance(lat, lon, lat + 1, lon);
        double lat_delta = wide / (2*lat_unit_distance);
        double lon_unit_distance = calculateDistance(lat, lon, lat, lon + 1);
        double lon_delta = wide / (2*lon_unit_distance);
        rectangle.x1 = lat - lat_delta;
        rectangle.x2 = lat + lat_delta;
        rectangle.y1 = lon - lon_delta;
        rectangle.y2 = lon + lon_delta;
		return rectangle;
	}
}
